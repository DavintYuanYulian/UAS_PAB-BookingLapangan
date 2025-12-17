import React, { useEffect, useState } from 'react';
import { router, usePage } from '@inertiajs/react';
import { api } from '../../lib/api';

export default function Index() {
  const { auth, pat } = usePage().props;
  const [items, setItems] = useState([]);
  const client = api();

  useEffect(() => {
    if (pat) sessionStorage.setItem('access_token', pat);
  }, [pat]);

  useEffect(() => {
    load();
  }, []);

  async function load() {
    const { data } = await client.get('/bookings');
    setItems(data);
  }

  async function disconnect() {
    await client.post('/oauth/logout');
    sessionStorage.removeItem('access_token');
    router.post('/logout');
  }

  return (
    <div className="min-h-screen bg-slate-100 p-6">
      <div className="max-w-6xl mx-auto space-y-6">

        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-2xl font-bold text-slate-800">
              Dashboard Booking Lapangan
            </h1>
            <p className="text-sm text-slate-500">
              Monitoring booking per hari
            </p>
          </div>

          <div className="flex items-center gap-3">
            <span className="text-sm text-slate-600">
              Login sebagai <b>{auth?.user?.name}</b>
            </span>
            <button
              onClick={disconnect}
              className="px-3 py-2 rounded-lg bg-white border hover:bg-slate-50"
            >
              Logout
            </button>
          </div>
        </div>

        {/* Table */}
        <div className="bg-white rounded-xl shadow overflow-hidden">
          <table className="w-full">
            <thead className="bg-slate-50 text-sm text-slate-600">
              <tr>
                <th className="p-3 text-left">Tanggal</th>
                <th className="p-3 text-center">Sisa Slot</th>
                <th className="p-3 text-center">Booked</th>
                <th className="p-3 text-center">Digunakan</th>
              </tr>
            </thead>
            <tbody>
              {items.map((item) => (
                <tr
                  key={item.date}
                  className="border-t hover:bg-slate-50"
                >
                  <td className="p-3 font-medium">
                    {item.date}
                  </td>

                  <td className="p-3 text-center">
                    <span
                      className={`px-2 py-1 rounded text-sm ${
                        item.remaining === 0
                          ? 'bg-red-100 text-red-700'
                          : 'bg-green-100 text-green-700'
                      }`}
                    >
                      {item.remaining}
                    </span>
                  </td>

                  <td className="p-3 text-center">
                    {item.booked}
                  </td>

                  <td className="p-3 text-center">
                    {item.used}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

      </div>
    </div>
  );
}





// // resources/js/Pages/Tours/Index.jsx
// import React, { useEffect, useState } from 'react';
// import { Link, router, usePage } from '@inertiajs/react';
// import { api } from '../../lib/api';

// export default function Index() {
//   const { auth, pat, pat_scopes } = usePage().props;
//   const [items, setItems] = useState([]);
//   const client = api();

//   // Save the PAT (shared once after login) so future requests use it
//   useEffect(() => {
//     if (pat) sessionStorage.setItem('access_token', pat);
//     // if(pat) console.log(pat);
//   }, [pat]);

// //   const canWrite  = pat_scopes?.includes('products:write');
// //   const canDelete = pat_scopes?.includes('products:delete');

//   useEffect(() => { load(); }, []);
//   async function load() {
//     const { data } = await client.get('/tours');
//     setItems(data);
//   }

// //   async function destroy(id) {
// //     await client.delete(`/products/${id}`);
// //     await load();
// //   }

//   async function disconnect() {
//     await client.post('/oauth/logout');
//     sessionStorage.removeItem('access_token');
//     router.post('/logout');
//   }

//   return (    
//     <div className="p-6 space-y-4">
//       <div className="flex items-center justify-between">
//         <h1 className="text-2xl font-bold">Tours</h1>
//         <div className="flex items-center gap-2">
//           <span className="text-sm text-slate-600">
//             Signed in as <b>{auth?.user?.name}</b> ({auth?.user?.role})
//           </span>
//           <button onClick={disconnect} className="px-3 py-2 rounded bg-slate-100">Sign out</button>
//         </div>
//       </div>

//       <table className="min-w-full border mt-3">
//         <thead><tr className="bg-slate-50">
//           <th className="p-2 border">#</th>
//           <th className="p-2 border">Name</th>
//           <th className="p-2 border">Price</th>
//           <th className="p-2 border">Stock</th>
//         </tr></thead>
//         <tbody>
//           {items.map(p => (
//             <tr key={p.tour_date.slice(0,10)}>
//               <td className="p-2 border">{p.tour_date.slice(0,10)}</td>
//               <td className="p-2 border">{p.remaining}</td>
//               <td className="p-2 border">{p.booked}</td>
//               <td className="p-2 border">{p.attended}</td>
//             </tr>
//           ))}
//         </tbody>
//       </table>
//     </div>
//   );
// }