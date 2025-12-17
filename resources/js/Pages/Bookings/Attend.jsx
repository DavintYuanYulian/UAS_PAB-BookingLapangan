// resources/js/Pages/Tours/Attend.jsx
import React, { useEffect } from 'react';
import { useForm, Link, router, usePage } from '@inertiajs/react';
import { api } from '../../lib/api';

export default function Form({ id = null }) {
  const { auth, pat, pat_scopes } = usePage().props;
  const { data, setData } = useForm({ client_name:'', tour_date: '2020-01-01' });
  const client = api();

  useEffect(() => { (async () => {
    if (!id) return;
    const item = await client.get(`/tours/${id}`);
    item.data.tour_date = item.data.tour_date.slice(0,10);
    if (item) setData(item.data);
  })(); }, [id]);

  const save = async (e) => {
    e.preventDefault();
    await client.post(`/tours/${id}`, data);
    window.location.href = '/tours';
  };

  async function disconnect() {
    await client.post('/oauth/logout');
    sessionStorage.removeItem('access_token');
    router.post('/logout');
  }

  return (
    <div className="p-6 space-y-4">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold">Attend Tours</h1>
        <div className="flex items-center gap-2">
          <span className="text-sm text-slate-600">
            Signed in as <b>{auth?.user?.name}</b> ({auth?.user?.role})
          </span>
          <button onClick={disconnect} className="px-3 py-2 rounded bg-slate-100">Sign out</button>
        </div>
      </div>

      <form onSubmit={save} className="space-y-3 max-w-md">
        <input className="w-full border p-2" placeholder="Nama"
               value={data.client_name} 
               onChange={e=>setData('client_name', e.target.value)}
               readOnly="readonly" />
        <input className="w-full border p-2" placeholder="Tanggal" type="date"
               value={data.tour_date} 
               onChange={e=>setData('tour_date', e.target.value)}
               readOnly="readonly" />
        <div className="flex gap-2">
          <button className="px-3 py-2 rounded bg-black text-white">Save</button>
          <Link className="px-3 py-2 rounded bg-slate-100" href={route('tours.index')}>Cancel</Link>
        </div>
      </form>
    </div>
  );
}