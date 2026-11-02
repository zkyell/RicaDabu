// Halaman menu: filter kategori, pencarian, navigasi bawah
let cat = "Semua";
function filterMenu(){
  const q = $("search").value.toLowerCase().trim(); let n = 0;
  document.querySelectorAll("#list .cd").forEach(c => {
    const ok = (cat==="Semua" || c.dataset.c===cat) && c.dataset.n.includes(q);
    c.hidden = !ok; if(ok) n++;
  });
  $("count").textContent = n + " Menu"; $("none").hidden = n > 0;
}
$("tabs").onclick = e => { const b=e.target.closest(".tab"); if(!b) return;
  document.querySelectorAll(".tab").forEach(t=>t.classList.remove("on")); b.classList.add("on"); cat=b.dataset.c; filterMenu(); };
$("search").oninput = filterMenu;
$("bn").onclick = e => { const b=e.target.closest("button"); if(!b) return; const n=b.dataset.n;
  if(n==="home") scrollTo({top:0,behavior:"smooth"});
  else if(n==="menu") $("menuTop").scrollIntoView({behavior:"smooth"});
  else if(n==="cart") showCart(true);
  else toast("Halaman profil segera hadir"); };
filterMenu();
if(new URLSearchParams(location.search).has("added")){ toast("Ditambahkan ke keranjang"); history.replaceState(null,"","menu.php"); }
