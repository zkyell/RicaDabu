// Keranjang di halaman menu (bar bawah + lembar keranjang)
let cart = Cart.get();
function updateCart(){
  Cart.set(cart); navBadge();
  const n = cart.reduce((a,c)=>a+c.qty,0), sum = cart.reduce((a,c)=>a+c.total,0);
  const adds = [...new Set(cart.flatMap(c=>c.adds||[]))];
  $("cbar").classList.toggle("on", n>0);
  $("bq").textContent = n; $("ct").textContent = `${n} Item • ${rp(sum)}`;
  $("cs").textContent = adds.length ? "Termasuk " + adds.join(", ").toLowerCase() : "Siap dipesan";
  $("tt").textContent = rp(sum);
  $("toCheckout").classList.toggle("off", !cart.length);
  $("cl").innerHTML = cart.length ? cart.map((c,i)=>`<div class="ci"><b>${esc(c.n)}</b><b>${rp(c.total)}</b>${c.detail?`<small>${esc(c.detail)}</small>`:""}
    <div class="cq"><button onclick="chg(${i},-1)" aria-label="Kurangi">−</button>${c.qty}<button onclick="chg(${i},1)" aria-label="Tambah">+</button></div></div>`).join("")
    : '<p class="empty">Keranjang masih kosong. Pilih menu dulu.</p>';
}
function chg(i,d){ const c=cart[i], u=c.total/c.qty; c.qty+=d; c.total=u*c.qty; if(c.qty<1) cart.splice(i,1); updateCart(); }
const showCart = s => $("ov").classList.toggle("show", s);
$("openCart").onclick = () => showCart(true);
$("cc").onclick = () => showCart(false);
$("ov").onclick = e => { if(e.target.id==="ov") showCart(false); };
document.addEventListener("keydown", e => { if(e.key==="Escape") showCart(false); });
updateCart();
