import React from 'react';
import { createRoot } from 'react-dom/client';

function App() {
  return <h1 className="text-3xl font-bold text-purple-600">Hello, Softmine!</h1>;
}

const root = createRoot(document.getElementById('app'));
root.render(<App />);
