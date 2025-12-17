// resources/js/Pages/Bookings/Use.jsx
import React, { useEffect } from 'react';
import { useForm, router, usePage } from '@inertiajs/react';
import { api } from '../../lib/api';

export default function Use({ id }) {
  const { auth, pat } = usePage().props;
  const { data, setData } = useForm({
    customer_name: '',
    booking_date: '',
  });

  const client = api();

  useEffect(() => {
    if (pat) sessionStorage.setItem('access_token', pat);
  }, [pat]);

  useEffect(() => {
    if (!id) return;
    client.get(`/bookings/${id}`).then(res => {
      res.data.booking_date = res.data.booking_date.slice(0,10);
      setData(res.data);
    });
  }, [id]);

  const submit = async (e) => {
    e.preventDefault();
    await client.post(`/bookings/${id}`);
    router.visit('/bookings');
  };

  return (
    <div className="p-6 max-w-md mx-auto">
      <h1 className="text-xl font-bold mb-4">Gunakan Booking</h1>

      <form onSubmit={submit} className="space-y-3">
        <input className="w-full border p-2" value={data.customer_name} readOnly />
        <input className="w-full border p-2" type="date" value={data.booking_date} readOnly />
        <button className="w-full bg-black text-white py-2 rounded">
          Tandai Digunakan
        </button>
      </form>
    </div>
  );
}
