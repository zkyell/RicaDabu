// Profil pelanggan: disimpan di browser (localStorage)
function fill(){
  const p = Profile.get();
  $("pName").value = p.name || ""; $("pPhone").value = p.phone || ""; $("pAddr").value = p.address || "";
  $("hName").textContent = p.name ? "Halo, " + p.name + "!" : "Halo!";
  $("avt").textContent = p.name ? p.name.trim()[0].toUpperCase() : "👤";
}
$("prof").onsubmit = e => {
  e.preventDefault();
  Profile.set({name:$("pName").value.trim(), phone:$("pPhone").value.trim(), address:$("pAddr").value.trim()});
  fill(); toast("Profil disimpan");
};
$("clear").onclick = () => {
  if(!confirm("Hapus profil, keranjang, dan riwayat pesanan dari HP ini?")) return;
  ["rd_profile","rd_cart","rd_orders"].forEach(k=>{ try{ localStorage.removeItem(k); }catch(e){} });
  fill(); navBadge(); toast("Data di HP ini dihapus");
};
fill();
