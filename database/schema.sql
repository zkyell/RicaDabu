-- Jalankan di Supabase: SQL Editor > New query > Run

create table menu_items (
  id bigint generated always as identity primary key,
  name text not null,
  price integer not null check (price >= 0),
  category text not null check (category in ('Makanan','Minuman','Pelengkap')),
  description text default '',
  image text default '',          -- nama file di folder images (tanpa .jpg)
  emoji text default '🍽️',
  is_hot boolean default false,
  is_available boolean default true,
  sort integer default 0
);

create table orders (
  id bigint generated always as identity primary key,
  customer_name text not null,
  phone text not null,
  total integer not null,
  status text not null default 'baru',   -- baru, diproses, selesai, batal
  created_at timestamptz not null default now()
);

create table order_items (
  id bigint generated always as identity primary key,
  order_id bigint not null references orders(id) on delete cascade,
  menu_item_id bigint references menu_items(id),
  name text not null,
  unit_price integer not null,
  qty integer not null check (qty > 0),
  spice text,
  addons text,
  note text
);

-- Keamanan: RLS aktif tanpa policy publik. Browser (anon key) tidak bisa
-- membaca/menulis. Hanya PHP (secret/service key) yang bisa mengakses.
alter table menu_items enable row level security;
alter table orders enable row level security;
alter table order_items enable row level security;

insert into menu_items (name, price, category, description, image, emoji, is_hot, sort) values
('Nasi Ayam Rica',20000,'Makanan','Ayam empuk dengan sambal rica pedas gurih, disajikan dengan nasi hangat.','nasi-ayam-rica','🍛',true,1),
('Nasi Ayam Bakar Dabu-Dabu',22000,'Makanan','Ayam bakar berbumbu dengan sambal dabu-dabu segar dan nasi.','nasi-ayam-bakar','🍗',true,2),
('Nasi Cakalang Rica',23000,'Makanan','Suwiran ikan cakalang berbumbu rica pedas gurih, dengan nasi.','nasi-cakalang-rica','🐟',true,3),
('Nasi Ikan Bakar Dabu-Dabu',25000,'Makanan','Ikan bakar segar disiram sambal dabu-dabu iris khas Manado, dengan nasi.','nasi-ikan-bakar','🔥',true,4),
('Rahang Tuna Dabu-Dabu',30000,'Makanan','Rahang tuna bakar dengan siraman dabu-dabu segar berlimpah.','rahang-tuna','🐠',true,5),
('Es Teh',5000,'Minuman','Teh manis dingin yang menyegarkan.','es-teh','🧋',false,6),
('Teh Hangat',5000,'Minuman','Teh manis hangat.','teh-hangat','🍵',false,7),
('Es Jeruk',8000,'Minuman','Jeruk peras segar dengan es.','es-jeruk','🍊',false,8),
('Lemon Tea',8000,'Minuman','Teh dingin dengan perasan lemon.','lemon-tea','🍋',false,9),
('Kopi Susu',7000,'Minuman','Kopi dengan susu, manis pas.','kopi-susu','☕',false,10),
('Air Mineral',6000,'Minuman','Air mineral botol.','air-mineral','💧',false,11),
('Perkedel Jagung',8000,'Pelengkap','Perkedel jagung manis, gurih, dan renyah.','perkedel-jagung','🌽',false,12),
('Tumis Kangkung',8000,'Pelengkap','Kangkung tumis segar.','tumis-kangkung','🥬',false,13),
('Tahu/Tempe',5000,'Pelengkap','Tahu atau tempe goreng.','tahu-tempe','🟨',false,14),
('Dabu-Dabu',5000,'Pelengkap','Sambal segar cabai, bawang merah, dan tomat.','dabu-dabu','🍅',true,15),
('Sambal Rica',5000,'Pelengkap','Sambal rica pedas gurih.','sambal-rica','🌶️',true,16);
