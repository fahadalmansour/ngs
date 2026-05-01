// Search Results + Quick View Overlay
const SearchResults = () => {
  const [quickView, setQuickView] = React.useState(null);
  const [query, setQuery] = React.useState('ubiquiti');

  const results = [
    {sku:'NG-ENT-003', ar:'Ubiquiti UniFi Dream Machine Pro', en:'UDM-Pro · 1U Rack', price:2054, was:2570, cat:'شبكات', ph:'UDM-Pro', score:98},
    {sku:'NT-WAP-UBQ-001', ar:'Ubiquiti UniFi U6 Pro', en:'WiFi 6 AP · AX5300', price:725, cat:'شبكات', ph:'U6 Pro', score:96},
    {sku:'NT-POE-UBQ-001', ar:'Ubiquiti USW-Lite-8-PoE', en:'8-Port PoE Switch', price:849, cat:'شبكات', ph:'USW-Lite', score:94},
    {sku:'NG-ENT-004', ar:'UniFi U6 Long Range', en:'U6-LR · High-Performance WiFi 6', price:938, was:1180, cat:'شبكات', ph:'U6-LR', score:91},
    {sku:'NT-NET-UBQ-001', ar:'Ubiquiti USW-Pro-24-PoE', en:'24-Port PoE Pro Switch', price:1889, was:2370, cat:'شبكات', ph:'USW-Pro-24', score:89},
    {sku:'NG-SEC-002', ar:'UniFi Protect G4 Pro Camera', en:'4K · PoE · NVR-ready', price:2008, was:2520, cat:'مراقبة', ph:'G4 Pro', score:85},
  ];

  return (
    <div dir="rtl" style={{background:'var(--bg)', minHeight:'100%', fontFamily:'var(--f-ar)'}}>
      <TopBar/>
      <Header/>

      {/* Search bar */}
      <section style={{
        borderBottom:'1px solid var(--rule)',
        background:`linear-gradient(rgba(10,10,10,0.03) 1px,transparent 1px),linear-gradient(90deg,rgba(10,10,10,0.03) 1px,transparent 1px),var(--bg)`,
        backgroundSize:'48px 48px, 48px 48px, auto'
      }}>
        <div style={{maxWidth:1440, margin:'0 auto', padding:'40px 48px'}}>
          <div style={{display:'flex', gap:12, alignItems:'center', marginBottom:24}}>
            <div style={{
              flex:1, display:'flex', alignItems:'center', gap:12,
              background:'var(--surface)', border:'1px solid var(--rule-strong)',
              borderRadius:'var(--r-2)', padding:'14px 20px',
              boxShadow:'var(--shadow-md)'
            }}>
              <span style={{fontSize:18, opacity:0.4}}>⌕</span>
              <input
                value={query}
                onChange={e=>setQuery(e.target.value)}
                style={{
                  flex:1, border:'none', outline:'none', background:'transparent',
                  fontFamily:'var(--f-ar)', fontSize:18, color:'var(--ink)'
                }}
                placeholder="ابحث عن منتج، علامة، أو SKU..."
              />
              <span className="mono-up" style={{color:'var(--dim)', fontSize:10}}>{results.length} نتيجة</span>
            </div>
            <button className="btn btn-dark" style={{borderRadius:'var(--r-2)', padding:'14px 24px'}}>بحث</button>
          </div>
          {/* Filter chips */}
          <div style={{display:'flex', gap:8, flexWrap:'wrap', alignItems:'center'}}>
            <span className="mono-up" style={{color:'var(--dim)', fontSize:9}}>تصفية:</span>
            {['الكل','الشبكات','البيت الذكي','هوم لاب','الألعاب','متوفر الآن','تخفيض'].map((f,i)=>(
              <button key={i} className={i===0?'chip chip-solid':'chip'} style={{padding:'6px 12px', cursor:'pointer'}}>{f}</button>
            ))}
            <span style={{marginInlineStart:'auto'}}>
              <select style={{fontFamily:'var(--f-ar)', fontSize:13, border:'1px solid var(--rule)', borderRadius:'var(--r-1)', padding:'6px 10px', background:'var(--surface)'}}>
                <option>الأكثر صلة</option>
                <option>السعر: من الأقل</option>
                <option>الأحدث</option>
              </select>
            </span>
          </div>
        </div>
      </section>

      <div style={{maxWidth:1440, margin:'0 auto', padding:'40px 48px 96px'}}>
        <div style={{display:'grid', gridTemplateColumns:'repeat(3,1fr)', gap:16}}>
          {results.map((r,i)=>(
            <article key={i} className="card" style={{cursor:'pointer'}} onClick={()=>setQuickView(r)}>
              <div className="ph" style={{aspectRatio:'4/3', borderRadius:'var(--r-2) var(--r-2) 0 0', border:'none', borderBottom:'1px solid var(--rule)'}}>
                <span className="ph-label">{r.ph}</span>
              </div>
              <div style={{padding:16}}>
                <div style={{display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:8}}>
                  <span className="mono" style={{fontSize:10, color:'var(--dim)'}}>{r.sku}</span>
                  <span className="chip">{r.cat}</span>
                </div>
                <h3 style={{margin:'0 0 4px', fontSize:15, fontWeight:600, color:'var(--indigo)'}}>{r.ar}</h3>
                <div style={{fontFamily:'var(--f-wordmark)', fontSize:11, color:'var(--ink-4)', marginBottom:12}}>{r.en}</div>
                <div style={{display:'flex', justifyContent:'space-between', alignItems:'flex-end'}}>
                  <div className="price">
                    <span className="price-now">{r.price.toLocaleString('en-US')}</span>
                    <span className="price-sar">SAR</span>
                    {r.was && <span className="price-was">{r.was.toLocaleString('en-US')}</span>}
                  </div>
                  <div style={{display:'flex', gap:6}}>
                    <button className="btn btn-ghost btn-sm" style={{borderRadius:'var(--r-1)', padding:'6px 10px', fontSize:11}} onClick={e=>{e.stopPropagation();setQuickView(r);}}>عرض سريع</button>
                    <button className="btn btn-sm" style={{borderRadius:'var(--r-1)', padding:'6px 10px', fontSize:11}} onClick={e=>e.stopPropagation()}>+ سلة</button>
                  </div>
                </div>
              </div>
            </article>
          ))}
        </div>
      </div>

      {/* Quick View Overlay */}
      {quickView && (
        <div style={{
          position:'fixed', inset:0, background:'rgba(15,23,42,0.7)', backdropFilter:'blur(8px)',
          zIndex:1000, display:'grid', placeItems:'center', padding:32
        }} onClick={()=>setQuickView(null)}>
          <div style={{
            background:'var(--surface)', borderRadius:'var(--r-3)', padding:0,
            maxWidth:860, width:'100%', maxHeight:'85vh', overflow:'auto',
            boxShadow:'var(--shadow-xl, 0 32px 72px rgba(15,23,42,0.35))'
          }} onClick={e=>e.stopPropagation()}>
            <div style={{display:'grid', gridTemplateColumns:'1fr 1fr'}}>
              <div className="ph" style={{aspectRatio:'1/1', borderRadius:'var(--r-3) 0 0 var(--r-3)', border:'none', borderInlineEnd:'1px solid var(--rule)'}}>
                <span className="ph-label">{quickView.ph}</span>
              </div>
              <div style={{padding:32, display:'flex', flexDirection:'column', gap:16}}>
                <div style={{display:'flex', justifyContent:'space-between', alignItems:'flex-start'}}>
                  <div>
                    <div className="mono" style={{fontSize:10, color:'var(--dim)', marginBottom:6}}>{quickView.sku} · {quickView.cat}</div>
                    <h2 style={{margin:0, fontSize:22, fontWeight:700, color:'var(--indigo)', lineHeight:1.2}}>{quickView.ar}</h2>
                    <div style={{fontFamily:'var(--f-wordmark)', fontSize:13, color:'var(--ink-4)', marginTop:4}}>{quickView.en}</div>
                  </div>
                  <button onClick={()=>setQuickView(null)} style={{fontSize:20, color:'var(--dim)', cursor:'pointer', background:'none', border:'none', padding:4}}>✕</button>
                </div>
                <div style={{width:40, height:2, background:'var(--accent)', borderRadius:2}}></div>
                <div className="price" style={{margin:'4px 0'}}>
                  <span className="price-now" style={{fontSize:28}}>{quickView.price.toLocaleString('en-US')}</span>
                  <span className="price-sar" style={{fontSize:14}}>SAR</span>
                  {quickView.was && <span className="price-was">{quickView.was.toLocaleString('en-US')}</span>}
                </div>
                <div style={{display:'grid', gridTemplateColumns:'1fr 1fr 1fr', gap:0, border:'1px solid var(--rule)', borderRadius:'var(--r-1)', overflow:'hidden'}}>
                  {[['متوفر','● نعم'],['شحن','2–5 أيام'],['ضمان','12 شهر']].map(([k,v],i)=>(
                    <div key={i} style={{padding:'10px 12px', borderInlineEnd: i<2?'1px solid var(--rule)':'none'}}>
                      <div className="mono-up" style={{color:'var(--dim)', fontSize:9}}>{k}</div>
                      <div style={{fontWeight:600, fontSize:12, color: i===0?'var(--good)':'var(--indigo)', marginTop:3}}>{v}</div>
                    </div>
                  ))}
                </div>
                <div style={{display:'flex', gap:8, marginTop:'auto'}}>
                  <button className="btn" style={{flex:1, justifyContent:'center', borderRadius:'var(--r-2)'}}>أضف للسلة</button>
                  <button className="btn btn-ghost" style={{borderRadius:'var(--r-2)', padding:'12px'}}>♡</button>
                </div>
                <a href="#" style={{textAlign:'center', fontSize:13, color:'var(--accent-deep)', fontWeight:600}}>عرض الصفحة الكاملة →</a>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
window.SearchResults = SearchResults;
