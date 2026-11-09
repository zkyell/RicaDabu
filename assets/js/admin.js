// Panel kasir: konfirmasi aksi penting + muat ulang otomatis untuk pesanan baru
document.addEventListener("click", e => {
  const b = e.target.closest("[data-confirm]");
  if(b && !confirm(b.dataset.confirm)) e.preventDefault();
});
const auto = document.getElementById("auto");
if(auto){
  try{ auto.checked = localStorage.getItem("rd_admin_auto") !== "0"; }catch(e){}
  auto.onchange = () => { try{ localStorage.setItem("rd_admin_auto", auto.checked ? "1" : "0"); }catch(e){} };
  setInterval(() => {
    const busy = document.activeElement && ["SELECT","INPUT"].includes(document.activeElement.tagName) && document.activeElement !== auto;
    if(auto.checked && !busy && !document.hidden) location.replace(location.pathname + location.search.replace(/([?&])ok=[^&]*/, "$1"));
  }, 20000);
}
// Pesan sukses hilang sendiri
const flash = document.querySelector(".flash");
if(flash) setTimeout(() => flash.remove(), 3500);
