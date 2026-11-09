-- ============================================================
--  UPGRADE untuk database yang SUDAH ADA (dari schema.sql lama).
--  Menambah kolom pembayaran, jenis pesanan, kode pesanan, dll.
--  Aman dijalankan lebih dari sekali.
--  Supabase: SQL Editor > New query > tempel > Run
-- ============================================================

alter table orders add column if not exists code text;
alter table orders add column if not exists order_type text not null default 'dine_in';
alter table orders add column if not exists table_no text;
alter table orders add column if not exists address text;
alter table orders add column if not exists note text;
alter table orders add column if not exists subtotal integer not null default 0;
alter table orders add column if not exists delivery_fee integer not null default 0;
alter table orders add column if not exists payment_method text not null default 'tunai';
alter table orders add column if not exists payment_status text not null default 'belum_bayar';
alter table orders add column if not exists payment_proof text;
alter table orders add column if not exists payment_ref text;
alter table orders add column if not exists paid_at timestamptz;
alter table orders add column if not exists updated_at timestamptz not null default now();

-- Pesanan lama diberi kode supaya tetap bisa dilacak
update orders set code = 'RD-' || upper(substr(md5(id::text || created_at::text), 1, 6)) where code is null;
update orders set subtotal = total where subtotal = 0;

do $$ begin
  alter table orders add constraint orders_code_key unique (code);
exception when duplicate_object or duplicate_table then null; end $$;

alter table orders drop constraint if exists orders_order_type_check;
alter table orders add constraint orders_order_type_check
  check (order_type in ('dine_in','takeaway','delivery'));
alter table orders drop constraint if exists orders_status_check;
alter table orders add constraint orders_status_check
  check (status in ('baru','diproses','siap','diantar','selesai','batal'));
alter table orders drop constraint if exists orders_payment_method_check;
alter table orders add constraint orders_payment_method_check
  check (payment_method in ('tunai','qris','transfer','ewallet','midtrans'));
alter table orders drop constraint if exists orders_payment_status_check;
alter table orders add constraint orders_payment_status_check
  check (payment_status in ('belum_bayar','menunggu_verifikasi','lunas','ditolak','gagal'));

create index if not exists orders_created_idx on orders (created_at desc);
create index if not exists order_items_order_idx on order_items (order_id);

create or replace function set_updated_at() returns trigger language plpgsql as $$
begin new.updated_at = now(); return new; end $$;
drop trigger if exists orders_updated on orders;
create trigger orders_updated before update on orders
  for each row execute function set_updated_at();

insert into storage.buckets (id, name, public)
values ('bukti-bayar', 'bukti-bayar', false)
on conflict (id) do nothing;
