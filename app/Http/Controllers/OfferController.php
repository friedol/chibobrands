<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Offer;
use App\Models\EnhancedProduct;
use Illuminate\Support\Facades\Validator;

class OfferController extends Controller
{
    /**
     * Store a newly created offer
     */
    public function store(Request $request)
    {
        // Debug: Log the incoming request
        \Log::info('Offer Store Request', [
            'all_data' => $request->all(),
            'headers' => $request->headers->all(),
            'method' => $request->method(),
            'url' => $request->url()
        ]);
        
        // Validate the request
        $validator = Validator::make($request->all(), [
            'product_barcode' => 'required|string|exists:enhanced_products,barcode',
            'offer_type' => 'required|string|in:percentage,fixed,buy_x_get_y,bulk_discount',
            'target_channel' => 'required|string|in:retail,wholesale,both',
            'discount_value' => 'required|numeric|min:0',
            'min_quantity' => 'nullable|numeric|min:0',
            'free_quantity' => 'nullable|numeric|min:0',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            \Log::info('Validation failed', [
                'errors' => $validator->errors()->toArray()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if product exists
            $product = EnhancedProduct::where('barcode', $request->product_barcode)->first();
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            // Validate discount value based on offer type
            if ($request->offer_type === 'percentage' && $request->discount_value > 100) {
                return response()->json([
                    'success' => false,
                    'message' => 'Percentage discount cannot exceed 100%'
                ], 422);
            }

            // Check for existing active offers for the same product and channel
            $existingOffer = Offer::where('product_barcode', $request->product_barcode)
                ->where('target_channel', $request->target_channel)
                ->where('is_active', true)
                ->where(function($query) use ($request) {
                    $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                          ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                          ->orWhere(function($q) use ($request) {
                              $q->where('start_date', '<=', $request->start_date)
                                ->where('end_date', '>=', $request->end_date);
                          });
                })
                ->first();

            if ($existingOffer) {
                return response()->json([
                    'success' => false,
                    'message' => 'An active offer already exists for this product and channel during the specified period'
                ], 422);
            }

            // Create the offer
            $offer = Offer::create([
                'product_barcode' => $request->product_barcode,
                'offer_type' => $request->offer_type,
                'target_channel' => $request->target_channel,
                'discount_value' => $request->discount_value,
                'min_quantity' => $request->min_quantity,
                'free_quantity' => $request->free_quantity,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'description' => $request->description,
                'is_active' => $request->has('is_active') ? $request->is_active : true
            ]);

            \Log::info('Offer created successfully', [
                'offer_id' => $offer->id,
                'product_barcode' => $offer->product_barcode
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Offer created successfully',
                'offer' => $offer
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error creating offer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the offer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get offers for a specific product
     */
    public function getProductOffers($productBarcode)
    {
        try {
            $offers = Offer::where('product_barcode', $productBarcode)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'offers' => $offers
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching offers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an offer
     */
    public function update(Request $request, $id)
    {
        try {
            $offer = Offer::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'offer_type' => 'sometimes|string|in:percentage,fixed,buy_x_get_y,bulk_discount',
                'target_channel' => 'sometimes|string|in:retail,wholesale,both',
                'discount_value' => 'sometimes|numeric|min:0',
                'min_quantity' => 'nullable|numeric|min:0',
                'free_quantity' => 'nullable|numeric|min:0',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date|after:start_date',
                'description' => 'nullable|string|max:1000',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $offer->update($request->only([
                'offer_type', 'target_channel', 'discount_value', 'min_quantity',
                'free_quantity', 'start_date', 'end_date', 'description', 'is_active'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Offer updated successfully',
                'offer' => $offer
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating offer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an offer
     */
    public function destroy($id)
    {
        try {
            $offer = Offer::findOrFail($id);
            $offer->delete();

            return response()->json([
                'success' => true,
                'message' => 'Offer deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting offer',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
