<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

class CustomerJourneyService
{
    /**
     * Automatically link matching leads to customer by phone number.
     */
    public static function linkLeadsToCustomer(Customer $customer): int
    {
        if (empty($customer->phone)) {
            return 0;
        }

        $normalizedPhone = PhoneNormalizationService::normalize($customer->phone);
        $normalizedWhatsapp = !empty($customer->whatsapp_number) ? PhoneNormalizationService::normalize($customer->whatsapp_number) : null;

        $query = Lead::whereNull('customer_id')
            ->where(function ($q) use ($normalizedPhone, $normalizedWhatsapp) {
                $q->where('phone', $normalizedPhone);
                if ($normalizedWhatsapp) {
                    $q->orWhere('phone', $normalizedWhatsapp);
                }
            });

        $count = $query->count();
        if ($count > 0) {
            $query->update([
                'customer_id'  => $customer->id,
                'status'       => 'converted',
                'converted_at' => now(),
            ]);
        }

        return $count;
    }

    /**
     * Convert all pending leads that belong to this customer (by customer_id or matching phone).
     * Call this whenever a customer makes a payment or places an order.
     */
    public static function convertLeadsByCustomer(Customer $customer): int
    {
        $converted = 0;

        // 1. Leads already linked by customer_id but still pending
        $byId = Lead::where('customer_id', $customer->id)
            ->where('status', 'pending')
            ->get();

        if ($byId->isNotEmpty()) {
            Lead::whereIn('id', $byId->pluck('id'))->update([
                'status'       => 'converted',
                'converted_at' => now(),
            ]);
            $converted += $byId->count();
        }

        // 2. Leads matched by phone but not yet linked to this customer
        if (!empty($customer->phone)) {
            $normalizedPhone = PhoneNormalizationService::normalize($customer->phone);
            $byPhone = Lead::where('phone', $normalizedPhone)
                ->where('status', 'pending')
                ->where(function ($q) use ($customer) {
                    $q->whereNull('customer_id')
                      ->orWhere('customer_id', '!=', $customer->id);
                })
                ->get();

            if ($byPhone->isNotEmpty()) {
                Lead::whereIn('id', $byPhone->pluck('id'))->update([
                    'customer_id'  => $customer->id,
                    'status'       => 'converted',
                    'converted_at' => now(),
                ]);
                $converted += $byPhone->count();
            }
        }

        return $converted;
    }

    /**
     * Find groups of duplicate customers based on normalized phone format.
     */
    public static function findDuplicateCustomers()
    {
        $allCustomers = Customer::whereNotNull('phone')
            ->where('phone', '!=', '')
            ->get();

        $groups = [];
        foreach ($allCustomers as $customer) {
            $norm = PhoneNormalizationService::normalize($customer->phone);
            if (!empty($norm)) {
                $groups[$norm][] = $customer;
            }
        }

        $duplicateGroups = collect();
        foreach ($groups as $normalizedPhone => $customers) {
            if (count($customers) > 1) {
                $duplicateGroups->push([
                    'phone' => $normalizedPhone,
                    'total' => count($customers),
                    'customers' => collect($customers),
                ]);
            }
        }

        return $duplicateGroups;
    }

    /**
     * Merge Customer B into Customer A (transferring all records).
     */
    public static function mergeCustomers(Customer $targetCustomer, Customer $sourceCustomer, int $mergedByUserId): bool
    {
        if ($targetCustomer->id === $sourceCustomer->id) {
            throw new \InvalidArgumentException('Target and source customer cannot be the same.');
        }

        DB::transaction(function () use ($targetCustomer, $sourceCustomer, $mergedByUserId) {
            // 1. Transfer Design Tasks
            DB::table('design_tasks')->where('customer_id', $sourceCustomer->id)->update(['customer_id' => $targetCustomer->id]);

            // 2. Transfer Payments
            DB::table('payments')->where('customer_id', $sourceCustomer->id)->update(['customer_id' => $targetCustomer->id]);

            // 3. Transfer Leads
            DB::table('leads')->where('customer_id', $sourceCustomer->id)->update(['customer_id' => $targetCustomer->id]);

            // 4. Transfer Customer Follow-ups
            DB::table('customer_follow_ups')->where('customer_id', $sourceCustomer->id)->update(['customer_id' => $targetCustomer->id]);

            // 5. Transfer SMS Campaign Recipients
            DB::table('sms_campaign_recipients')->where('customer_id', $sourceCustomer->id)->update(['customer_id' => $targetCustomer->id]);

            // 6. Transfer Businesses
            DB::table('customer_businesses')->where('customer_id', $sourceCustomer->id)->update(['customer_id' => $targetCustomer->id]);

            // 7. Transfer Analytics
            DB::table('customer_product_analytics')->where('customer_id', $sourceCustomer->id)->update(['customer_id' => $targetCustomer->id]);
            DB::table('customer_task_type_analytics')->where('customer_id', $sourceCustomer->id)->update(['customer_id' => $targetCustomer->id]);

            // Update Target Customer Aggregates
            $targetCustomer->increment('total_orders', $sourceCustomer->total_orders ?? 0);
            $targetCustomer->increment('total_spent', $sourceCustomer->total_spent ?? 0);
            $targetCustomer->increment('purchase_count', $sourceCustomer->purchase_count ?? 0);

            // Audit log record
            if (class_exists(AuditLog::class)) {
                AuditLog::create([
                    'user_id'     => $mergedByUserId,
                    'action'      => 'CUSTOMER_MERGE',
                    'description' => "Merged duplicate customer #{$sourceCustomer->id} ({$sourceCustomer->name}) into primary customer #{$targetCustomer->id} ({$targetCustomer->name}).",
                    'ip_address'  => request()->ip() ?? '127.0.0.1',
                    'user_agent'  => request()->userAgent() ?? 'System',
                ]);
            }

            // Soft-delete or delete source customer
            $sourceCustomer->delete();
        });

        return true;
    }
}
