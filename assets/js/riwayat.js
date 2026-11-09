// Riwayat pesanan dari HP ini
const TYPE = {dine_in:"🍽️ Makan di tempat", takeaway:"🛍 Ambil sendiri", delivery:"🛵 Diantar"};
async function load(){
  const codes = MyOrders.get();
  if(!codes.length){
    $("list").innerHTML = '<section class="blk empty-st"><p class="big">🧾</p><h2>Belum ada pesanan</h2><p>Pesanan yang kamu buat dari HP ini akan muncul di sini.</p><a class="red btn" href="menu.php">Pesan sekarang</a></section>';
    return;
  }
  try{
    const r = await fetch("api/status.php?kode=" + codes.join(","), {cache:"no-store"});
    const list = await r.json();
    if(!r.ok) throw new Error(list.error);
    $("list").innerHTML = list.map(o => `<a class="blk oc" href="pesanan.php?kode=${o.code}">
        <div class="bh"><b>${o.code}</b><span class="badge s-${o.status}">${esc(o.status_label)}</span></div>
        <small>${new Date(o.created_at).toLocaleString("id-ID",{dateStyle:"medium",timeStyle:"short"})} · ${TYPE[o.order_type]||""} · ${o.items} menu</small>
        <div class="bh"><b class="rpx">${rp(o.total)}</b><span class="badge b-${o.payment_status}">${esc(o.payment_label)}</span></div>
      </a>`).join("") || '<p class="hint center">Pesanan tidak ditemukan.</p>';
  }catch(e){ $("list").innerHTML = '<p class="hint center">Gagal memuat pesanan. Periksa koneksi internet lalu muat ulang.</p>'; }
}
$("find").onsubmit = e => {
  e.preventDefault();
  const k = $("kode").value.trim().toUpperCase().replace(/^RD-?/, "");
  if(!/^[A-Z0-9]{6}$/.test(k)){ toast("Format kode: RD-XXXXXX"); return; }
  location.href = "pesanan.php?kode=RD-" + k;
};
load();
