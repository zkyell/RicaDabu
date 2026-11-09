// Fungsi bantu yang dipakai semua halaman
const $ = id => document.getElementById(id);
const rp = n => "Rp " + Number(n).toLocaleString("id-ID");
const esc = s => String(s ?? "").replace(/[&<>"']/g, c => ({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[c]));
function toast(t){ const e=$("toast"); if(!e) return; e.textContent=t; e.classList.add("show");
  clearTimeout(toast.t); toast.t=setTimeout(()=>e.classList.remove("show"),1800); }

// Data disimpan di browser supaya tetap ada saat pindah halaman
const store = (key, def) => ({
  get(){ try{ return JSON.parse(localStorage.getItem(key)) ?? def; }catch(e){ return def; } },
  set(v){ try{ localStorage.setItem(key, JSON.stringify(v)); }catch(e){} }
});
const Cart    = store("rd_cart", []);     // isi keranjang
const MyOrders = store("rd_orders", []);  // kode pesanan yang pernah dibuat di HP ini
const Profile = store("rd_profile", {});  // nama, HP, alamat pelanggan

// Angka kecil di ikon Keranjang (navigasi bawah & header desktop)
function navBadge(){
  const n = Cart.get().reduce((a,c)=>a+c.qty,0);
  document.querySelectorAll("[data-qty]").forEach(b => { b.textContent = n; b.hidden = n < 1; });
}
navBadge();

// Tombol "Salin" (kode pesanan, nomor rekening, nominal)
document.addEventListener("click", async e => {
  const b = e.target.closest("[data-copy]"); if(!b) return;
  e.preventDefault();
  try{ await navigator.clipboard.writeText(b.dataset.copy); toast("Disalin: " + b.dataset.copy); }
  catch(err){ prompt("Salin teks ini:", b.dataset.copy); }
});
