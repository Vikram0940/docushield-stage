/* =========================
   URL & Storage Utilities
   ========================= */
const qs = new URLSearchParams(location.search);
const DOC_ID   = qs.get('doc')   || 'demoDoc-001';
let   LEFT_ID  = qs.get('left')  || null;
let   RIGHT_ID = qs.get('right') || null;

const Store = {
  key(){ return `doc:${DOC_ID}:versions`; },
  all(){ return JSON.parse(localStorage.getItem(this.key()) || '[]'); },
  get(id){ return this.all().find(v => String(v.id) === String(id)); },
  add(content, meta={}){ // helper, not used here
    const list = this.all();
    list.push({ id: Date.now(), ts: new Date().toISOString(), title: meta.title || 'Untitled', author: meta.author || 'You', content });
    localStorage.setItem(this.key(), JSON.stringify(list));
  }
};

// Change Endpoints here for live API
// const VersionsAPI = {
//   async list()  { return fetch(`/api/docs/${DOC_ID}/versions`).then(r=>r.json()); },
//   async get(id) { return fetch(`/api/docs/${DOC_ID}/versions/${id}`).then(r=>r.json()); },
//   async restore(versionId){
//     return fetch(`/api/docs/${DOC_ID}/versions/${versionId}/restore`, {method:'POST'}).then(r=>r.json());
//   }
// };


// Adapter you can swap to your API later:
const VersionsAPI = {
  async list(){ return Store.all(); },
  async get(id){ return Store.get(id); },
  async restore(versionId){
    // TODO: Call your backend to restore. For demo, just stash a flag.
    sessionStorage.setItem(`restore:${DOC_ID}`, versionId);
    return { ok:true };
  }
};

/* =========================
   Minimal word-level diff
   (LCS-based; fine for mid documents)
   ========================= */
function tokenize(s){ return (s||'').replace(/\r/g,'').split(/(\s+|[.,!?;:()"'“”‘’\[\]{}])/).filter(Boolean); }
function lcs(a,b){
  const n=a.length, m=b.length;
  const dp=Array(n+1); for(let i=0;i<=n;i++){ dp[i]=Array(m+1).fill(0); }
  for(let i=n-1;i>=0;i--) for(let j=m-1;j>=0;j--) dp[i][j]=a[i]===b[j]?dp[i+1][j+1]+1:Math.max(dp[i+1][j],dp[i][j+1]);
  const out=[]; let i=0,j=0;
  while(i<n && j<m){
    if(a[i]===b[j]){ out.push({type:'=', a:a[i], b:b[j]}); i++; j++; }
    else if(dp[i+1][j]>=dp[i][j+1]){ out.push({type:'-', a:a[i++]}); }
    else { out.push({type:'+', b:b[j++]}); }
  }
  while(i<n) out.push({type:'-', a:a[i++]});
  while(j<m) out.push({type:'+', b:b[j++]});
  return out;
}
function renderDiff(leftText, rightText){
  const A = tokenize(leftText), B = tokenize(rightText);
  const seq = lcs(A,B);
  const L=[], R=[];
  seq.forEach(p=>{
    if(p.type==='='){ const t=p.a; L.push(esc(t)); R.push(esc(t)); }
    if(p.type==='-'){ L.push(`<mark class="diff-removed">${esc(p.a)}</mark>`); }
    if(p.type==='+' ){ R.push(`<mark class="diff-added">${esc(p.b)}</mark>`); }
  });
  return { left: L.join(''), right: R.join('') };
}
function esc(s){ return (s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function dt(ts){ const d=new Date(ts); return isFinite(+d)? d.toLocaleString(): '—'; }

/* =========================
   DOM refs
   ========================= */
try {
  const $ = (sel, root=document)=>root.querySelector(sel);
  const versionsCol = $('#versionsCol');
  const leftDiff    = $('#leftDiff');
  const rightDiff   = $('#rightDiff');
  const leftSel     = $('#leftSelect');
  const rightSel    = $('#rightSelect');
  const leftLabel   = $('#leftLabel');
  const rightLabel  = $('#rightLabel');
  const leftMeta    = $('#leftMeta');
  const rightMeta   = $('#rightMeta');
  const revCount    = $('#revCount');
  const docTitleEl  = $('#docTitle');
  const backLink    = $('#backLink');
  const restoreBtn  = $('#restoreBtn');
}
catch ($ex) {
}
/* =========================
   Renderers
   ========================= */
let VERSIONS = []; // filled on boot

async function boot(){
  VERSIONS = await VersionsAPI.list();
  // If no versions yet, seed with one from local editor session if present
  if (!VERSIONS.length){
    Store.add('<p>Start writing…</p>', {title:'Untitled'});
    VERSIONS = await VersionsAPI.list();
  }

  const latest = VERSIONS[VERSIONS.length-1];
  docTitleEl.value = latest?.title || 'Untitled';
  revCount.textContent = VERSIONS.length;

  // Defaults for left/right
  RIGHT_ID = RIGHT_ID || (latest && latest.id);
  LEFT_ID  = LEFT_ID  || (VERSIONS[VERSIONS.length-2] && VERSIONS[VERSIONS.length-2].id) || RIGHT_ID;

  // Build selects + versions list
  fillSelects();
  renderVersionsList();
  await showPair(LEFT_ID, RIGHT_ID);

  // Wire events
  leftSel.onchange  = handlePickChange;
  rightSel.onchange = handlePickChange;
  restoreBtn.onclick = handleRestore;

  // Back link to main doc page (adjust route as needed)
  backLink.href = `/index.html?doc=${encodeURIComponent(DOC_ID)}`;
}
function fillSelects(){
  function optionHtml(v){
    return `<option value="${v.id}">${dt(v.ts)} — ${esc(v.title)}</option>`;
  }
  leftSel.innerHTML  = VERSIONS.map(optionHtml).join('');
  rightSel.innerHTML = VERSIONS.map(optionHtml).join('');
  leftSel.value  = LEFT_ID;
  rightSel.value = RIGHT_ID;
}
function renderVersionsList(){
  versionsCol.innerHTML = VERSIONS.slice().reverse().map(v=>`
    <label class="ver d-flex align-items-start gap-2">
      <input class="form-check-input pick" type="checkbox" value="${v.id}">
      <div>
        <div class="fw-semibold text-light">${esc(v.title)}</div>
        <div class="meta">${dt(v.ts)} &middot; ${esc(v.author || '')}</div>
      </div>
    </label>
  `).join('');

  // pick exactly two → set left/right
  versionsCol.addEventListener('change', function(e){
    if (!e.target.classList.contains('pick')) return;
    const picks = Array.from(versionsCol.querySelectorAll('.pick:checked')).map(el=>el.value);
    if (picks.length>2) { e.target.checked=false; return; }
    if (picks.length===2){
      LEFT_ID  = picks[1]; // older on left
      RIGHT_ID = picks[0]; // newer on right
      leftSel.value  = LEFT_ID;
      rightSel.value = RIGHT_ID;
      showPair(LEFT_ID, RIGHT_ID);
      syncURL();
    }
  }, { once:true }); // set once; you can remove {once:true} if you want persistent behavior
}
async function showPair(leftId, rightId){
  const L = await VersionsAPI.get(leftId);
  const R = await VersionsAPI.get(rightId);
  if (!L || !R) return;

  const {left, right} = renderDiff(L.content, R.content);
  leftDiff.innerHTML  = left;
  rightDiff.innerHTML = right;

  leftLabel.textContent  = `Revision — ${esc(L.title || '')}`;
  rightLabel.textContent = `Revision — ${esc(R.title || '')}${rightId==VERSIONS[VERSIONS.length-1].id?' (Current)':''}`;

  leftMeta.textContent  = dt(L.ts);
  rightMeta.textContent = dt(R.ts);

  restoreBtn.disabled = (rightId==VERSIONS[VERSIONS.length-1].id); // can't restore current
}
function handlePickChange(){
  LEFT_ID  = leftSel.value;
  RIGHT_ID = rightSel.value;
  showPair(LEFT_ID, RIGHT_ID);
  syncURL();
}
function syncURL(){
  const u = new URL(location.href);
  u.searchParams.set('doc', DOC_ID);
  u.searchParams.set('left', LEFT_ID);
  u.searchParams.set('right', RIGHT_ID);
  history.replaceState(null,'',u.toString());
}
async function handleRestore(){
  if (!confirm('Restore the RIGHT revision into the live document?')) return;
  const ok = await VersionsAPI.restore(RIGHT_ID);
  if (ok && ok.ok){
    alert('Restored (demo). Implement server restore in VersionsAPI.restore().');
    // Optionally redirect to editor:
    // location.href = `/index.html?doc=${encodeURIComponent(DOC_ID)}`
  }
}

boot();