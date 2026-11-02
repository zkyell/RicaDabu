// Keranjang (bar bawah + lembar keranjang) dan pengiriman pesanan ke api/order.php
let cart = Cart.get();
function updateCart(){
  Cart.set(cart);
  const n = cart.reduce((a,c)=>a+c.qty,0), sum = cart.reduce((a,c)=>a+c.total,0);
  const adds = [...new Set(cart.flatMap(c=>c.adds))];
  $("cbar").classList.toggle("on", n>0);
  $("bq").textContent = n; $("ct").textContent = `${n} Item • ${rp(sum)}`;
  $("cs").textContent = adds.length ? "Termasuk " + adds.join(", ").toLowerCase() : "Siap dipesan";
  $("tt").textContent = rp(sum);
  $("cl").innerHTML = cart.length ? cart.map((c,i)=>`<div class="ci"><b>${c.n}</b><b>${rp(c.total)}</b>${c.detail?`<small>${c.detail}</small>`:""}
    <div class="cq"><button onclick="chg(${i},-1)" aria-label="Kurangi">−</button>${c.qty}<button onclick="chg(${i},1)" aria-label="Tambah">+</button></div></div>`).join("")
    : '<p class="empty">Keranjang masih kosong. Pilih menu dulu.</p>';
}
function chg(i,d){ const c=cart[i], u=c.total/c.qty; c.qty+=d; c.total=u*c.qty; if(c.qty<1) cart.splice(i,1); updateCart(); }
const showCart = s => $("ov").classList.toggle("show", s);
$("openCart").onclick = () => showCart(true);
$("cc").onclick = () => showCart(false);
$("ov").onclick = e => { if(e.target.id==="ov") showCart(false); };
document.addEventListener("keydown", e => { if(e.key==="Escape") showCart(false); });

$("send").onclick = async () => {
  const msg=$("msg"), btn=$("send"); msg.style.color="var(--r)";
  if(!cart.length){ msg.textContent="Keranjang masih kosong."; return; }
  if(!$("cN").value.trim() || $("cP").value.replace(/\D/g,"").length<8){ msg.textContent="Isi nama dan nomor HP dengan benar."; return; }
  btn.disabled=true; btn.textContent="Mengirim...";
  try{
    const r = await fetch("api/order.php",{method:"POST",headers:{"Content-Type":"application/json"},
      body:JSON.stringify({name:$("cN").value.trim(),phone:$("cP").value,items:cart.map(c=>({id:c.id,qty:c.qty,adds:c.adds,sp:c.sp,note:c.note}))})});
    const j = await r.json();
    if(!r.ok) throw new Error(j.error||"Gagal mengirim pesanan");
    cart=[]; updateCart(); msg.style.color="#2E7D32"; msg.textContent="Pesanan terkirim! Nomor pesanan #"+j.order_id;
  }catch(e){ msg.textContent=e.message; }
  btn.disabled=false; btn.textContent="Kirim Pesanan";
};
updateCart();
