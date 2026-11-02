// Halaman detail menu: jumlah, pilihan, tambah ke keranjang
const D = $("detail").dataset, price = +D.price;
let qty = 1;
const total = () => { let x=0; document.querySelectorAll("#dAdd input:checked").forEach(c=>x+=+c.dataset.p); return (price+x)*qty; };
const refresh = () => { $("qty").textContent = qty; $("addCart").textContent = "🛍  Tambah   " + rp(total()); };
$("plus").onclick  = () => { qty++; refresh(); };
$("minus").onclick = () => { if(qty>1){ qty--; refresh(); } };
$("fav").onclick   = () => { const f=$("fav"); f.textContent = f.textContent==="♡" ? "♥" : "♡"; };
$("note").oninput  = e => $("cnt").textContent = e.target.value.length ? e.target.value.length+"/120 huruf" : "Maks. 120 huruf";
if($("dAdd")) $("dAdd").onchange = refresh;
$("addCart").onclick = () => {
  const adds = [...document.querySelectorAll("#dAdd input:checked")].map(c=>c.value);
  const r = document.querySelector("input[name=sp]:checked"), sp = r ? r.value : "";
  const note = $("note").value.trim();
  const cart = Cart.get();
  cart.push({id:+D.id, n:D.name, adds, sp, note, qty, total:total(),
    detail:[sp, adds.length?"+ "+adds.join(", "):"", note].filter(Boolean).join(" • ")});
  Cart.set(cart);
  location.href = "menu.php?added=1";
};
refresh();
