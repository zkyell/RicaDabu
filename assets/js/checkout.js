// Halaman checkout: keranjang, cara pesan, data pemesan, metode pembayaran
const C = $("checkout").dataset, FEE = +C.fee, FREE = +C.free;
let cart = Cart.get();
const form = $("form");
const val = n => (form.querySelector(`[name=${n}]:checked`) || {}).value;

function feeFor(sub){ return val("order_type")==="delivery" ? ((FREE>0 && sub>=FREE) ? 0 : FEE) : 0; }

function render(){
  Cart.set(cart); navBadge();
  $("items").innerHTML = cart.length ? cart.map((c,i)=>`<div class="li">
      <div><b>${esc(c.n)}</b>${c.detail?`<small>${esc(c.detail)}</small>`:""}<span class="up">${rp(c.total/c.qty)}</span></div>
      <div class="cq"><button type="button" data-i="${i}" data-d="-1" aria-label="Kurangi ${esc(c.n)}">−</button><b>${c.qty}</b><button type="button" data-i="${i}" data-d="1" aria-label="Tambah ${esc(c.n)}">+</button></div>
    </div>`).join("")
    : '<div class="empty-st"><p class="big">🛍</p><p>Keranjang masih kosong.</p><a class="red btn" href="menu.php">Pilih menu</a></div>';
  const sub = cart.reduce((a,c)=>a+c.total,0), fee = feeFor(sub);
  $("sSub").textContent = rp(sub);
  $("rowFee").hidden = val("order_type")!=="delivery";
  $("sFee").textContent = fee ? rp(fee) : "Gratis";
  $("sTot").textContent = $("bTot").textContent = rp(sub+fee);
  form.hidden = !cart.length; document.querySelector(".bar").hidden = !cart.length;
}

$("items").onclick = e => {
  const b = e.target.closest("button[data-i]"); if(!b) return;
  const c = cart[+b.dataset.i], u = c.total/c.qty;
  c.qty += +b.dataset.d; c.total = u*c.qty;
  if(c.qty < 1) cart.splice(+b.dataset.i, 1);
  render();
};

function syncType(){
  const t = val("order_type");
  document.querySelectorAll("[data-for]").forEach(d => d.hidden = d.dataset.for !== t);
  const cash = form.querySelector("[name=payment_method][value=tunai]");
  $("cashHint").hidden = !(t==="delivery" && cash && cash.checked);
  render();
}
form.addEventListener("change", syncType);

// Isi otomatis dari profil
const p = Profile.get();
$("name").value = p.name || ""; $("phone").value = p.phone || ""; $("address").value = p.address || "";

form.onsubmit = async e => {
  e.preventDefault();
  const msg = $("msg"), btn = $("submit"), t = val("order_type");
  const data = {
    name: $("name").value.trim(), phone: $("phone").value.trim(), order_type: t,
    table_no: $("table_no").value.trim(), address: $("address").value.trim(), note: $("note").value.trim(),
    payment_method: val("payment_method"),
    items: cart.map(c=>({id:c.id, qty:c.qty, adds:c.adds, sp:c.sp, note:c.note}))
  };
  const err = !cart.length ? "Keranjang masih kosong."
    : t==="dine_in" && !data.table_no ? "Isi nomor meja kamu."
    : t==="delivery" && data.address.length<10 ? "Isi alamat pengantaran lengkap."
    : !data.name ? "Isi nama kamu."
    : data.phone.replace(/\D/g,"").length<8 ? "Isi nomor HP/WhatsApp yang benar."
    : !data.payment_method ? "Pilih metode pembayaran." : "";
  if(err){ msg.textContent = err; msg.scrollIntoView({behavior:"smooth", block:"center"}); return; }

  btn.disabled = true; btn.textContent = "Membuat pesanan…"; msg.textContent = "";
  try{
    const r = await fetch("api/order.php", {method:"POST", headers:{"Content-Type":"application/json"}, body:JSON.stringify(data)});
    const j = await r.json();
    if(!r.ok) throw new Error(j.error || "Gagal membuat pesanan");
    Profile.set({...Profile.get(), name:data.name, phone:data.phone, ...(data.address?{address:data.address}:{})});
    MyOrders.set([j.code, ...MyOrders.get().filter(c=>c!==j.code)].slice(0,30));
    Cart.set([]);
    location.href = "pesanan.php?kode=" + encodeURIComponent(j.code) + "&baru=1";
  }catch(ex){
    msg.textContent = ex.message; btn.disabled = false; btn.textContent = "Buat Pesanan";
  }
};
syncType();
