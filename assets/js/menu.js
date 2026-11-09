// Halaman menu: filter kategori, pencarian, urutkan
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

// Tombol urutkan: Rekomendasi → Termurah → Termahal
const sorts = ["Rekomendasi", "Termurah", "Termahal"];
const cards = [...document.querySelectorAll("#list .cd")];
let si = 0;
$("sort").onclick = () => {
  si = (si + 1) % sorts.length;
  const list = [...cards];
  if(si===1) list.sort((a,b)=>a.dataset.p-b.dataset.p);
  if(si===2) list.sort((a,b)=>b.dataset.p-a.dataset.p);
  list.forEach(c => $("list").insertBefore(c, $("none")));
  toast("Urutan: " + sorts[si]);
};

filterMenu();
if(new URLSearchParams(location.search).has("added")){ toast("Ditambahkan ke keranjang"); history.replaceState(null,"","menu.php"); }
