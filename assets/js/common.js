// Fungsi bantu yang dipakai semua halaman
const $ = id => document.getElementById(id);
const rp = n => "Rp " + n.toLocaleString("id-ID");
function toast(t){ const e=$("toast"); if(!e) return; e.textContent=t; e.classList.add("show");
  clearTimeout(toast.t); toast.t=setTimeout(()=>e.classList.remove("show"),1800); }
// Keranjang disimpan di browser supaya tetap ada saat pindah halaman
const Cart = {
  get(){ try{ return JSON.parse(localStorage.getItem("rd_cart"))||[]; }catch(e){ return []; } },
  set(c){ try{ localStorage.setItem("rd_cart", JSON.stringify(c)); }catch(e){} }
};
