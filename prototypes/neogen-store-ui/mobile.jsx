// Mobile screens — Homepage, Gift Cards, PDP in iPhone frames
const MobileHomepage = () => (
  <div dir="rtl" style={{fontFamily:'var(--f-ar)', background:'var(--bg)', minHeight:'100%', fontSize:13}}>
    {/* Hero */}
    <div style={{
      padding:'32px 20px 24px',
      background:`radial-gradient(400px circle at 60% 30%, rgba(56,189,248,0.1), transparent 60%),
        linear-gradient(rgba(10,10,10,0.03) 1px,transparent 1px),
        linear-gradient(90deg,rgba(10,10,10,0.03) 1px,transparent 1px),#F8FAFC`,
      backgroundSize:'auto,24px 24px,24px 24px,auto',
      borderBottom:'1px solid var(--rule)'
    }}>
      <div style={{display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:28}}>
        <img src="assets/ng-mark.png" alt="NG" style={{height:28, width:'auto'}}/>
        <div style={{display:'flex', gap:8}}>
          <button style={{background:'none', border:'1px solid var(--rule)', borderRadius:'var(--r-1)', padding:'6px 10px', fontSize:12}}>⌕</button>
          <button className="btn btn-dark btn-sm" style={{borderRadius:'var(--r-1)', padding:'6px 12px', fontSize:12}}>السلة 02</button>
        </div>
      </div>
      <div style={{display:'inline-flex', alignItems:'center', gap:6, padding:'4px 10px', border:'1px solid var(--rule)', borderRadius:'var(--r-pill)', marginBottom:16}}>
        <span className="dot dot-on"></span>
        <span className="mono-up" style={{fontSize:9, color:'var(--ink-4)'}}>متجر تقني سعودي · 2026</span>
      </div>
      <h1 style={{margin:'0 0 12px', fontFamily:'var(--f-wordmark)', fontSize:36, fontWeight:700, lineHeight:0.95, letterSpacing:'-0.02em', color:'var(--indigo)'}}>
        جيل<br/><span style={{color:'var(--accent)', fontStyle:'italic', fontWeight:400}}>النِّقلة</span><br/>التقنية.
      </h1>
      <div style={{width:40, height:3, background:'var(--accent)', borderRadius:2, margin:'16px 0'}}></div>
      <p style={{fontSize:13, lineHeight:1.6, color:'var(--ink-3)', margin:'0 0 20px'}}>
        وحدات مختارة للشبكات، الهوم لاب، البيوت الذكية، والألعاب. شحن من المملكة.
      </p>
      <div style={{display:'flex', gap:8}}>
        <button className="btn btn-sm" style={{borderRadius:'var(--r-2)', flex:1, justifyContent:'center'}}>تصفّح المتجر →</button>
        <button className="btn btn-ghost btn-sm" style={{borderRadius:'var(--r-2)'}}>ابنِ جهازك</button>
      </div>
      <div style={{display:'flex', gap:8, marginTop:16, flexWrap:'wrap'}}>
        {['🇸🇦','🇦🇪','🇰🇼','🇧🇭','🇴🇲','🇶🇦'].map((f,i)=><span key={i} style={{fontSize:16}}>{f}</span>)}
      </div>
    </div>

    {/* Categories strip */}
    <div style={{padding:'20px 16px', borderBottom:'1px solid var(--rule)'}}>
      <div className="section-mark" style={{marginBottom:14, fontSize:9}}><span>01</span><span style={{color:'var(--ink)'}}>· الفئات</span></div>
      <div style={{display:'flex', gap:8, overflowX:'auto'}} className="no-scrollbar">
        {[['بطاقات رقمية','Gift Cards',83],['البيت الذكي','Smart Home',51],['الألعاب','Gaming',39],['هوم لاب','Homelab',29],['الشبكات','Networking',26]].map(([ar,en,n],i)=>(
          <div key={i} style={{
            flexShrink:0, padding:'12px 14px', borderRadius:'var(--r-2)',
            border: i===0?'1px solid var(--accent-deep)':'1px solid var(--rule)',
            background: i===0?'var(--accent-wash)':'var(--surface)',
            minWidth:120
          }}>
            <div className="mono-up" style={{fontSize:8, color: i===0?'var(--accent-deep)':'var(--dim)'}}>{n} منتج</div>
            <div style={{fontWeight:700, fontSize:13, color:'var(--indigo)', marginTop:4}}>{ar}</div>
            <div style={{fontFamily:'var(--f-wordmark)', fontSize:10, color:'var(--ink-4)'}}>{en}</div>
          </div>
        ))}
      </div>
    </div>

    {/* Product cards */}
    <div style={{padding:'20px 16px'}}>
      <div className="section-mark" style={{marginBottom:14, fontSize:9}}><span>02</span><span style={{color:'var(--ink)'}}>· المختارات</span></div>
      <div style={{display:'flex', flexDirection:'column', gap:12}}>
        {SAMPLE_PRODUCTS.slice(0,3).map((p,i)=>(
          <div key={i} style={{
            display:'grid', gridTemplateColumns:'80px 1fr', gap:0,
            border:'1px solid var(--rule)', borderRadius:'var(--r-2)', overflow:'hidden', background:'var(--surface)'
          }}>
            <div className="ph" style={{height:'100%', borderRadius:0, border:'none', borderInlineEnd:'1px solid var(--rule)'}}>
              <span className="ph-label" style={{fontSize:8}}>{p.ph}</span>
            </div>
            <div style={{padding:12}}>
              <div className="mono" style={{fontSize:9, color:'var(--dim)'}}>{p.sku}</div>
              <div style={{fontWeight:600, fontSize:13, color:'var(--indigo)', margin:'4px 0'}}>{p.ar}</div>
              <div style={{display:'flex', justifyContent:'space-between', alignItems:'center'}}>
                <div className="price"><span className="price-now" style={{fontSize:14}}>{p.price.toLocaleString('en-US')}</span><span className="price-sar">SAR</span></div>
                <button className="btn btn-sm" style={{padding:'5px 10px', fontSize:11, borderRadius:'var(--r-1)'}}>+</button>
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>

    {/* Bottom nav */}
    <div style={{
      position:'sticky', bottom:0, background:'rgba(248,250,252,0.95)', backdropFilter:'blur(12px)',
      borderTop:'1px solid var(--rule)', display:'grid', gridTemplateColumns:'repeat(5,1fr)', padding:'8px 0 4px'
    }}>
      {[['🏠','الرئيسية'],['🛍','المتجر'],['🎁','بطاقات'],['❤️','المفضلة'],['👤','حسابي']].map(([icon,label],i)=>(
        <div key={i} style={{display:'flex', flexDirection:'column', alignItems:'center', gap:3, cursor:'pointer'}}>
          <span style={{fontSize:18}}>{icon}</span>
          <span style={{fontFamily:'var(--f-mono)', fontSize:9, textTransform:'uppercase', color: i===0?'var(--accent-ink)':'var(--dim)'}}>{label}</span>
        </div>
      ))}
    </div>
  </div>
);

const MobileGiftCards = () => {
  const [active, setActive] = React.useState('all');
  const regions = [
    {k:'all',f:'⊕',n:'الكل'},{k:'KSA',f:'🇸🇦',n:'KSA'},{k:'UAE',f:'🇦🇪',n:'UAE'},
    {k:'KW',f:'🇰🇼',n:'KW'},{k:'BH',f:'🇧🇭',n:'BH'},{k:'OM',f:'🇴🇲',n:'OM'},
    {k:'QA',f:'🇶🇦',n:'QA'},{k:'US',f:'🇺🇸',n:'US'},{k:'UK',f:'🇬🇧',n:'UK'},{k:'GLB',f:'🌐',n:'Global'},
  ];
  const cards = [
    {b:'PlayStation',d:'12 شهر',price:399,region:'KSA',flag:'🇸🇦',color:'#003791',fg:'#fff',psn:true},
    {b:'Apple',d:'$100',price:389,region:'US',flag:'🇺🇸',color:'#1d1d1f',fg:'#fff'},
    {b:'Xbox',d:'3 أشهر',price:149,region:'KSA',flag:'🇸🇦',color:'#107C10',fg:'#fff'},
    {b:'Spotify',d:'$60',price:239,region:'GLB',flag:'🌐',color:'#1DB954',fg:'#000'},
    {b:'Netflix',d:'$100',price:389,region:'US',flag:'🇺🇸',color:'#E50914',fg:'#fff'},
    {b:'Google Play',d:'٢٠٠ ر.س',price:200,region:'KSA',flag:'🇸🇦',color:'#fff',fg:'#000',border:true},
  ];
  return (
    <div dir="rtl" style={{fontFamily:'var(--f-ar)', background:'var(--bg)', minHeight:'100%'}}>
      <div style={{padding:'16px 16px 12px', borderBottom:'1px solid var(--rule)', display:'flex', justifyContent:'space-between', alignItems:'center', background:'var(--surface)'}}>
        <img src="assets/ng-mark.png" alt="NG" style={{height:24}}/>
        <h2 style={{margin:0, fontSize:15, fontWeight:700, color:'var(--indigo)'}}>بطاقات رقمية</h2>
        <button style={{border:'1px solid var(--rule)', borderRadius:'var(--r-1)', padding:'4px 8px', fontSize:11}}>⌕</button>
      </div>
      {/* Region scroll */}
      <div style={{display:'flex', gap:6, padding:'12px 16px', overflowX:'auto', borderBottom:'1px solid var(--rule)'}} className="no-scrollbar">
        {regions.map((r,i)=>(
          <button key={i} onClick={()=>setActive(r.k)} style={{
            flexShrink:0, display:'flex', flexDirection:'column', alignItems:'center', gap:3,
            padding:'8px 10px', borderRadius:'var(--r-2)', cursor:'pointer',
            border: active===r.k?'1px solid var(--indigo)':'1px solid var(--rule)',
            background: active===r.k?'var(--indigo)':'var(--surface)',
            color: active===r.k?'#fff':'var(--ink)'
          }}>
            <span style={{fontSize:16}}>{r.f}</span>
            <span style={{fontFamily:'var(--f-mono)', fontSize:9, textTransform:'uppercase'}}>{r.n}</span>
          </button>
        ))}
      </div>
      {/* PSN hero */}
      <div style={{margin:16, borderRadius:'var(--r-3)', background:'#003791', color:'#fff', padding:20, position:'relative', overflow:'hidden'}}>
        <img src="assets/playstation.webp" alt="" style={{position:'absolute', inset:0, width:'100%', height:'100%', objectFit:'cover', opacity:0.25}}/>
        <div style={{position:'relative'}}>
          <div style={{fontFamily:'var(--f-wordmark)', fontSize:12, color:'rgba(255,255,255,0.6)', marginBottom:6}}>PlayStation Plus · KSA</div>
          <h3 style={{margin:'0 0 12px', fontSize:24, fontWeight:700}}>12 شهر PS Plus</h3>
          <div style={{display:'flex', justifyContent:'space-between', alignItems:'center'}}>
            <div className="price"><span style={{fontSize:22, color:'#fff', fontFamily:'var(--f-wordmark)', fontWeight:700}}>399</span><span className="price-sar" style={{color:'rgba(255,255,255,0.6)'}}>SAR</span></div>
            <button className="btn btn-sm" style={{background:'#fff', color:'#003791', borderRadius:'var(--r-1)', boxShadow:'none', fontSize:12}}>أضف للسلة</button>
          </div>
        </div>
      </div>
      {/* Cards grid */}
      <div style={{padding:'0 16px 16px', display:'grid', gridTemplateColumns:'1fr 1fr', gap:10}}>
        {cards.slice(1).map((c,i)=>(
          <div key={i} style={{borderRadius:'var(--r-2)', overflow:'hidden', boxShadow:'var(--shadow-sm)', border: c.border?'1px solid var(--rule)':'none'}}>
            <div style={{background:c.color, color:c.fg, padding:'14px 12px', display:'flex', flexDirection:'column', justifyContent:'space-between', minHeight:90}}>
              <div style={{display:'flex', justifyContent:'space-between', fontSize:11}}>
                <span style={{fontFamily:'var(--f-wordmark)', fontWeight:700, fontSize:12}}>{c.b}</span>
                <span style={{fontSize:14}}>{c.flag}</span>
              </div>
              <div style={{fontFamily:'var(--f-wordmark)', fontWeight:700, fontSize:16}}>{c.d}</div>
            </div>
            <div style={{background:'var(--surface)', padding:'10px 12px', display:'flex', justifyContent:'space-between', alignItems:'center'}}>
              <div className="price"><span className="price-now" style={{fontSize:14}}>{c.price}</span><span className="price-sar">SAR</span></div>
              <button className="btn btn-sm" style={{padding:'4px 8px', fontSize:11, borderRadius:'var(--r-1)'}}>+</button>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

const MobilePDP = () => (
  <div dir="rtl" style={{fontFamily:'var(--f-ar)', background:'var(--bg)', minHeight:'100%'}}>
    <div style={{padding:'14px 16px', borderBottom:'1px solid var(--rule)', display:'flex', justifyContent:'space-between', alignItems:'center', background:'var(--surface)'}}>
      <span style={{fontSize:20, color:'var(--ink-3)'}}>←</span>
      <span className="mono" style={{fontSize:11, color:'var(--dim)'}}>NG-ENT-003 · UBIQUITI</span>
      <button style={{border:'none', background:'none', fontSize:18}}>♡</button>
    </div>
    <div className="ph" style={{aspectRatio:'1/1', borderRadius:0, border:'none', borderBottom:'1px solid var(--rule)'}}>
      <span className="ph-label">UDM-Pro · main</span>
    </div>
    <div style={{padding:16, background:'var(--surface)', borderBottom:'1px solid var(--rule)'}}>
      <div className="chip chip-sky" style={{marginBottom:10}}>● متوفر · 12 وحدة</div>
      <h1 style={{margin:'0 0 6px', fontSize:20, fontWeight:700, color:'var(--indigo)', lineHeight:1.2}}>
        Ubiquiti UniFi Dream Machine Pro
      </h1>
      <div style={{fontFamily:'var(--f-wordmark)', fontSize:12, color:'var(--ink-4)', marginBottom:16}}>All-in-One · 1U Rack · 8-port</div>
      <div style={{borderTop:'1px solid var(--rule)', borderBottom:'1px solid var(--rule)', padding:'14px 0', display:'flex', justifyContent:'space-between', alignItems:'center'}}>
        <div>
          <div className="price"><span className="price-now" style={{fontSize:28}}>2,054</span><span className="price-sar" style={{fontSize:13}}>SAR</span><span className="price-was">2,570</span></div>
          <div className="mono" style={{fontSize:10, color:'var(--dim)', marginTop:2}}>شامل الضريبة · وفّر 516 SAR</div>
        </div>
        <div className="mono-up" style={{fontSize:9, color:'var(--good)'}}>● شحن 2–5 أيام</div>
      </div>
      <div style={{display:'flex', gap:8, marginTop:14}}>
        <div style={{display:'flex', border:'1px solid var(--rule)', borderRadius:'var(--r-1)'}}>
          <button style={{padding:'10px 14px', fontSize:16}}>−</button>
          <span style={{padding:'10px 14px', fontFamily:'var(--f-wordmark)', fontWeight:700, borderInline:'1px solid var(--rule)'}}>1</span>
          <button style={{padding:'10px 14px', fontSize:16}}>+</button>
        </div>
        <button className="btn" style={{flex:1, justifyContent:'center', borderRadius:'var(--r-2)'}}>أضف للسلة</button>
      </div>
    </div>
    {/* Works best with */}
    <div style={{padding:'16px'}}>
      <div className="section-mark" style={{marginBottom:12, fontSize:9}}><span>A</span><span style={{color:'var(--ink)'}}>· يعمل مع</span></div>
      <div style={{display:'flex', gap:8, overflowX:'auto'}} className="no-scrollbar">
        {[{n:'UniFi U6 Pro',p:725,ph:'U6 Pro'},{n:'USW-Lite-8-PoE',p:849,ph:'switch'},{n:'SFP+ Transceiver',p:129,ph:'SFP+'}].map((w,i)=>(
          <div key={i} style={{flexShrink:0, width:140, border:'1px solid var(--rule)', borderRadius:'var(--r-2)', overflow:'hidden', background:'var(--surface)'}}>
            <div className="ph" style={{height:90, borderRadius:0, border:'none', borderBottom:'1px solid var(--rule)'}}>
              <span className="ph-label" style={{fontSize:8}}>{w.ph}</span>
            </div>
            <div style={{padding:'8px 10px'}}>
              <div style={{fontSize:12, fontWeight:600, color:'var(--indigo)', marginBottom:4}}>{w.n}</div>
              <div className="price"><span className="price-now" style={{fontSize:13}}>{w.p}</span><span className="price-sar">SAR</span></div>
            </div>
          </div>
        ))}
      </div>
    </div>
    {/* Add-ons */}
    <div style={{padding:'0 16px 16px'}}>
      <div className="section-mark" style={{marginBottom:12, fontSize:9}}><span>B</span><span style={{color:'var(--ink)'}}>· الإضافات والاستبدال</span></div>
      <div style={{display:'flex', flexDirection:'column', gap:8}}>
        {[{n:'كابل DAC Twinax 10G',p:80,t:'upgrade',ph:'DAC'},{n:'HDD WD Red Plus 4TB',p:699,t:'consumable',ph:'HDD'},{n:'أقواس Rack 1U',p:89,t:'spare',ph:'rack ears'}].map((r,i)=>(
          <div key={i} style={{display:'grid', gridTemplateColumns:'56px 1fr auto', gap:10, padding:'10px 12px', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', background:'var(--surface)', alignItems:'center'}}>
            <div className="ph" style={{height:56, borderRadius:'var(--r-1)'}}><span className="ph-label" style={{fontSize:7}}>{r.ph}</span></div>
            <div>
              <div style={{fontFamily:'var(--f-mono)', fontSize:9, textTransform:'uppercase', color:r.t==='upgrade'?'var(--accent-deep)':r.t==='consumable'?'var(--sale)':'var(--ink-4)', marginBottom:3}}>{r.t==='upgrade'?'ترقية':r.t==='consumable'?'استهلاكي':'قطعة غيار'}</div>
              <div style={{fontSize:13, fontWeight:600, color:'var(--indigo)'}}>{r.n}</div>
            </div>
            <div style={{textAlign:'end'}}>
              <div className="price"><span className="price-now" style={{fontSize:14}}>{r.p}</span><span className="price-sar">SAR</span></div>
              <button className="btn btn-sm" style={{padding:'4px 8px', fontSize:11, borderRadius:'var(--r-1)', marginTop:4}}>+</button>
            </div>
          </div>
        ))}
      </div>
    </div>
  </div>
);

Object.assign(window, {MobileHomepage, MobileGiftCards, MobilePDP});
