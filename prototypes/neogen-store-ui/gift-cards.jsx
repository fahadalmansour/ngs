// Gift Cards page — real brand system, multi-region, PSN real image
const GiftCards = () => {
  const [activeRegion, setActiveRegion] = React.useState('all');

  const regions = [
    {k:'all', f:'⊕', n:'الكل', count:83},
    {k:'KSA', f:'🇸🇦', n:'السعودية', count:38},
    {k:'UAE', f:'🇦🇪', n:'الإمارات', count:14},
    {k:'KW',  f:'🇰🇼', n:'الكويت',  count:6},
    {k:'BH',  f:'🇧🇭', n:'البحرين', count:4},
    {k:'OM',  f:'🇴🇲', n:'عُمان',   count:4},
    {k:'QA',  f:'🇶🇦', n:'قطر',     count:5},
    {k:'US',  f:'🇺🇸', n:'الولايات المتحدة', count:18},
    {k:'UK',  f:'🇬🇧', n:'المملكة المتحدة', count:11},
    {k:'GLB', f:'🌐', n:'عالمية',   count:9},
  ];

  const cards = [
    {b:'PlayStation', sku:'GC-PSP-KSA-12', ar:'PlayStation Plus', d:'12 شهر', price:399, region:'KSA', flag:'🇸🇦', color:'#003791', fg:'#fff', psn:true},
    {b:'PlayStation', sku:'GC-PSP-UAE-12', ar:'PlayStation Plus', d:'12 شهر', price:415, region:'UAE', flag:'🇦🇪', color:'#003791', fg:'#fff', psn:true},
    {b:'PlayStation', sku:'GC-PSP-US-12',  ar:'PlayStation Plus', d:'12 شهر', price:475, region:'US',  flag:'🇺🇸', color:'#003791', fg:'#fff', psn:true},
    {b:'PlayStation', sku:'GC-PSP-UK-12',  ar:'PlayStation Plus', d:'12 شهر', price:445, region:'UK',  flag:'🇬🇧', color:'#003791', fg:'#fff', psn:true},
    {b:'Apple', sku:'GC-APL-KSA-100', ar:'Apple Gift Card', d:'٤٠٠ ر.س', price:400, region:'KSA', flag:'🇸🇦', color:'#1d1d1f', fg:'#fff'},
    {b:'Apple', sku:'GC-APL-UAE-100', ar:'Apple Gift Card', d:'AED 400',  price:415, region:'UAE', flag:'🇦🇪', color:'#1d1d1f', fg:'#fff'},
    {b:'Apple', sku:'GC-APL-US-100',  ar:'Apple Gift Card', d:'$100',    price:389, region:'US',  flag:'🇺🇸', color:'#1d1d1f', fg:'#fff'},
    {b:'Steam', sku:'GC-STM-100',     ar:'بطاقة Steam',     d:'$100',    price:399, region:'GLB', flag:'🌐',  color:'#171a21', fg:'#66c0f4'},
    {b:'Xbox',  sku:'GC-XBX-GPU3-KSA',ar:'Game Pass Ultimate',d:'3 أشهر',price:149, region:'KSA', flag:'🇸🇦', color:'#107C10', fg:'#fff'},
    {b:'Xbox',  sku:'GC-XBX-GPU3-UK', ar:'Game Pass Ultimate',d:'3 أشهر',price:165, region:'UK',  flag:'🇬🇧', color:'#107C10', fg:'#fff'},
    {b:'Spotify',sku:'GC-SPF-60-GLB', ar:'Spotify Premium',  d:'$60',    price:239, region:'GLB', flag:'🌐',  color:'#1DB954', fg:'#000'},
    {b:'Netflix',sku:'GC-NFX-100-US', ar:'Netflix Card',     d:'$100',   price:389, region:'US',  flag:'🇺🇸', color:'#E50914', fg:'#fff'},
    {b:'Google Play',sku:'GC-GP-KSA-50',ar:'Google Play',   d:'٢٠٠ ر.س',price:200, region:'KSA', flag:'🇸🇦', color:'#fff', fg:'#000', border:true},
    {b:'Google Play',sku:'GC-GP-KW-50', ar:'Google Play',   d:'KWD 15',  price:195, region:'KW',  flag:'🇰🇼', color:'#fff', fg:'#000', border:true},
    {b:'Disney+',sku:'GC-DSP-US-25',  ar:'Disney+',         d:'$25',     price:99,  region:'US',  flag:'🇺🇸', color:'#0E2C68', fg:'#fff'},
    {b:'Adobe CC',sku:'GC-SW-ACC1',   ar:'Creative Cloud',  d:'سنة كاملة',price:1999,was:2499,region:'GLB',flag:'🌐',color:'#000',fg:'#FF0000'},
  ];

  const filtered = activeRegion === 'all' ? cards : cards.filter(c => c.region === activeRegion);

  return (
    <div dir="rtl" style={{background:'var(--bg)', minHeight:'100%', fontFamily:'var(--f-ar)'}}>
      <TopBar/>
      <Header/>

      {/* Hero */}
      <section style={{
        borderBottom:'1px solid var(--rule)',
        background:`radial-gradient(700px circle at 60% 40%, rgba(56,189,248,0.07), transparent 55%),
          linear-gradient(rgba(10,10,10,0.035) 1px, transparent 1px),
          linear-gradient(90deg, rgba(10,10,10,0.035) 1px, transparent 1px), #F8FAFC`,
        backgroundSize:'auto, 48px 48px, 48px 48px, auto'
      }}>
        <div style={{maxWidth:1440, margin:'0 auto', padding:'72px 48px 48px'}}>
          <div style={{display:'grid', gridTemplateColumns:'1.2fr 1fr', gap:64, alignItems:'end'}}>
            <div>
              <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:16}}>الفئة 06 · بطاقات رقمية ومفاتيح</div>
              <h1 className="t-h1" style={{margin:0, color:'var(--indigo)', textWrap:'balance'}}>
                بطاقات رقمية.<br/>
                <span style={{fontFamily:'var(--f-wordmark)', color:'var(--accent)', fontWeight:600}}>GCC · US · UK</span>
              </h1>
              <p style={{fontSize:16, lineHeight:1.65, color:'var(--ink-3)', maxWidth:520, marginTop:20}}>
                تسليم فوري للرمز عبر البريد بعد الدفع. كل بطاقة تحمل منطقة تفعيل واضحة — السعودية، الإمارات، الكويت، البحرين، عُمان، قطر، أو عالمية.
              </p>
            </div>
            <div style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-3)', padding:28, boxShadow:'var(--shadow-md)'}}>
              <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:20}}>CATALOG STATS · 2026</div>
              <div style={{display:'grid', gridTemplateColumns:'1fr 1fr', gap:24}}>
                {[['83','بطاقة'],['16','علامة'],['<60s','تسليم'],['10','مناطق']].map(([n,l],i)=>(
                  <div key={i}>
                    <div style={{fontFamily:'var(--f-wordmark)', fontSize:36, fontWeight:700, lineHeight:1, color:'var(--indigo)'}}>{n}</div>
                    <div className="mono-up" style={{color:'var(--dim)', marginTop:4}}>{l}</div>
                  </div>
                ))}
              </div>
            </div>
          </div>

          {/* Region filter */}
          <div style={{marginTop:48, borderTop:'1px solid var(--rule)', paddingTop:24}}>
            <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:14}}>اختر منطقة التفعيل · ACTIVATION REGION</div>
            <div style={{display:'grid', gridTemplateColumns:'repeat(10,1fr)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', overflow:'hidden'}}>
              {regions.map((r,i)=>(
                <button key={i} onClick={()=>setActiveRegion(r.k)} style={{
                  padding:'14px 8px',
                  borderInlineEnd: i<9?'1px solid var(--rule)':'none',
                  background: activeRegion===r.k ? 'var(--indigo)' : 'var(--surface)',
                  color: activeRegion===r.k ? '#fff' : 'var(--ink)',
                  border:'none', cursor:'pointer',
                  display:'flex', flexDirection:'column', gap:5, alignItems:'center',
                  transition:'background .15s'
                }}>
                  <span style={{fontSize:18, lineHeight:1}}>{r.f}</span>
                  <span style={{fontFamily:'var(--f-mono)', fontSize:9, textTransform:'uppercase', letterSpacing:'0.08em', color: activeRegion===r.k?'rgba(255,255,255,0.6)':'var(--dim)'}}>{r.k}</span>
                  <span style={{fontSize:11, fontWeight:500}}>{r.n.split('·')[0].trim()}</span>
                  <span style={{fontFamily:'var(--f-wordmark)', fontSize:11, opacity:0.55}}>{r.count}</span>
                </button>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* Featured — PSN with real image */}
      <section style={{maxWidth:1440, margin:'0 auto', padding:'72px 48px 48px'}}>
        <div className="section-mark" style={{marginBottom:24}}><span>01</span><span style={{color:'var(--ink)'}}>· مميّزة · FEATURED</span></div>
        <div style={{display:'grid', gridTemplateColumns:'1.2fr 1fr 1fr', gap:16}}>
          {/* PSN — real bg image */}
          <div style={{
            background:'#003791', color:'#fff', padding:32,
            position:'relative', overflow:'hidden',
            display:'flex', flexDirection:'column', justifyContent:'space-between',
            borderRadius:'var(--r-3)', minHeight:280
          }}>
            <img src="assets/playstation.webp" alt="PlayStation" style={{
              position:'absolute', inset:0, width:'100%', height:'100%',
              objectFit:'cover', opacity:0.3
            }}/>
            <div style={{position:'relative', display:'flex', justifyContent:'space-between'}}>
              <span className="mono-up" style={{color:'rgba(255,255,255,0.7)'}}>PSN · 12M · KSA</span>
              <span className="chip chip-sky">مميّزة</span>
            </div>
            <div style={{position:'relative'}}>
              <div style={{fontFamily:'var(--f-wordmark)', fontSize:13, color:'rgba(255,255,255,0.65)', marginBottom:8}}>PlayStation Plus</div>
              <h3 style={{margin:0, fontSize:38, fontWeight:700, lineHeight:0.95, letterSpacing:'-0.02em'}}>12 شهر<br/>اشتراك PS Plus</h3>
              <div style={{display:'flex', justifyContent:'space-between', alignItems:'flex-end', marginTop:28}}>
                <div className="price"><span style={{fontSize:28, color:'#fff', fontFamily:'var(--f-wordmark)', fontWeight:700}}>399</span><span className="price-sar" style={{color:'rgba(255,255,255,0.6)'}}>SAR</span></div>
                <button className="btn btn-sm" style={{background:'#fff', color:'#003791', borderRadius:'var(--r-1)', boxShadow:'none'}}>أضف للسلة →</button>
              </div>
            </div>
          </div>
          {/* Apple */}
          <div style={{background:'#1d1d1f', color:'#fff', padding:28, display:'flex', flexDirection:'column', justifyContent:'space-between', borderRadius:'var(--r-3)', position:'relative', overflow:'hidden'}}>
            <div style={{position:'absolute', inset:0, background:'radial-gradient(ellipse at 30% 80%, #3a3a3c 0%, transparent 60%)'}}></div>
            <div style={{position:'relative', display:'flex', justifyContent:'space-between'}}>
              <span style={{fontSize:32, lineHeight:1}}></span>
              <span className="mono-up" style={{color:'rgba(255,255,255,0.5)'}}>$100 · GCC</span>
            </div>
            <div style={{position:'relative'}}>
              <h3 style={{margin:0, fontSize:28, fontWeight:700, lineHeight:1}}>Apple Gift Card</h3>
              <div style={{fontSize:13, color:'rgba(255,255,255,0.6)', marginTop:8}}>App Store · iCloud · Music</div>
              <div className="price" style={{marginTop:20}}><span style={{fontSize:22, color:'#fff', fontFamily:'var(--f-wordmark)', fontWeight:700}}>389</span><span className="price-sar" style={{color:'rgba(255,255,255,0.5)'}}>SAR</span></div>
            </div>
          </div>
          {/* Steam */}
          <div style={{background:'#171a21', color:'#fff', padding:28, display:'flex', flexDirection:'column', justifyContent:'space-between', borderRadius:'var(--r-3)'}}>
            <div style={{display:'flex', justifyContent:'space-between'}}>
              <span style={{fontFamily:'var(--f-wordmark)', color:'#66c0f4', fontSize:22, fontWeight:700, letterSpacing:'-0.02em'}}>STEAM</span>
              <span className="mono-up" style={{color:'rgba(255,255,255,0.5)'}}>$100 · GLOBAL</span>
            </div>
            <div>
              <h3 style={{margin:0, fontSize:28, fontWeight:700, lineHeight:1}}>بطاقة Steam</h3>
              <div style={{fontSize:13, color:'rgba(255,255,255,0.6)', marginTop:8}}>للألعاب على PC</div>
              <div className="price" style={{marginTop:20}}><span style={{fontSize:22, color:'#fff', fontFamily:'var(--f-wordmark)', fontWeight:700}}>399</span><span className="price-sar" style={{color:'rgba(255,255,255,0.5)'}}>SAR</span></div>
            </div>
          </div>
        </div>
      </section>

      {/* All cards grid */}
      <section style={{maxWidth:1440, margin:'0 auto', padding:'0 48px 96px'}}>
        <div style={{display:'flex', justifyContent:'space-between', alignItems:'flex-end', marginBottom:28}}>
          <div>
            <div className="section-mark" style={{marginBottom:14}}><span>02</span><span style={{color:'var(--ink)'}}>· كل البطاقات · ALL CARDS</span></div>
            <h2 className="t-h2" style={{margin:0, color:'var(--indigo)'}}>تصفّح حسب العلامة والمنطقة.</h2>
          </div>
          <div className="mono-up" style={{color:'var(--dim)'}}>{filtered.length} بطاقة معروضة</div>
        </div>
        <div style={{display:'grid', gridTemplateColumns:'repeat(4,1fr)', gap:16}}>
          {filtered.map((c,i)=>(
            <article key={i} style={{
              background:'var(--surface)', border: c.border ? '1px solid var(--rule)' : 'none',
              borderRadius:'var(--r-2)', overflow:'hidden', boxShadow:'var(--shadow-sm)'
            }}>
              {/* Card visual */}
              <div style={{
                background: c.psn
                  ? `url(assets/playstation.webp) center/cover, ${c.color}`
                  : c.color,
                color:c.fg, padding:20, position:'relative',
                display:'flex', flexDirection:'column', justifyContent:'space-between',
                minHeight:130
              }}>
                {c.psn && <div style={{position:'absolute', inset:0, background:'rgba(0,55,145,0.55)'}}></div>}
                <div style={{position:'relative', display:'flex', justifyContent:'space-between', alignItems:'flex-start'}}>
                  <span style={{fontFamily:'var(--f-wordmark)', fontSize:14, fontWeight:700, color:c.fg}}>{c.b}</span>
                  <span style={{
                    fontFamily:'var(--f-mono)', fontSize:9, padding:'3px 8px', textTransform:'uppercase',
                    letterSpacing:'0.07em',
                    background: c.color==='#fff' ? 'var(--indigo)' : 'rgba(255,255,255,0.18)',
                    color: c.color==='#fff' ? '#fff' : c.fg,
                    border: '1px solid '+(c.color==='#fff' ? 'var(--indigo)' : 'rgba(255,255,255,0.3)'),
                    borderRadius:2, display:'inline-flex', gap:5, alignItems:'center'
                  }}>{c.flag} {c.region}</span>
                </div>
                <div style={{position:'relative'}}>
                  <div style={{fontFamily:'var(--f-mono)', fontSize:10, opacity:0.5, letterSpacing:'0.2em', marginBottom:4}}>•••• •••• •••• ••••</div>
                  <div style={{fontFamily:'var(--f-wordmark)', fontSize:18, fontWeight:700, lineHeight:1, color:c.fg}}>{c.d}</div>
                </div>
              </div>
              <div style={{padding:14, borderTop: c.border ? '1px solid var(--rule)' : 'none', background:'var(--surface)'}}>
                <div style={{display:'flex', justifyContent:'space-between', marginBottom:6}}>
                  <span className="mono" style={{fontSize:10, color:'var(--dim)'}}>{c.sku}</span>
                  <span style={{fontFamily:'var(--f-mono)', fontSize:9, textTransform:'uppercase', letterSpacing:'0.08em', color:'var(--good)'}}>● فوري</span>
                </div>
                <div style={{fontWeight:600, fontSize:13, color:'var(--indigo)', marginBottom:10}}>{c.ar} · {c.d}</div>
                <div style={{display:'flex', justifyContent:'space-between', alignItems:'baseline'}}>
                  <div className="price">
                    <span className="price-now" style={{fontSize:16}}>{c.price.toLocaleString('en-US')}</span>
                    <span className="price-sar">SAR</span>
                    {c.was && <span className="price-was">{c.was.toLocaleString('en-US')}</span>}
                  </div>
                  <button className="btn btn-sm" style={{padding:'6px 10px', fontSize:11, borderRadius:'var(--r-1)'}}>أضف +</button>
                </div>
              </div>
            </article>
          ))}
        </div>
        {/* Region note */}
        <div style={{
          marginTop:28, padding:'18px 24px',
          border:'1px solid var(--rule)', borderRadius:'var(--r-2)',
          display:'flex', justifyContent:'space-between', alignItems:'center', gap:24,
          background:'var(--surface)'
        }}>
          <div style={{fontSize:14, color:'var(--ink-3)'}}>
            <span className="mono-up" style={{color:'var(--accent-deep)', marginInlineEnd:10}}>ملاحظة مهمة</span>
            كل بطاقة مربوطة بمنطقة تفعيل محددة. تأكد من اختيار منطقة تطابق حساب متجرك.
          </div>
          <span className="mono-up" style={{color:'var(--ink-4)', whiteSpace:'nowrap', fontSize:9}}>KSA · UAE · KW · BH · OM · QA · US · UK · Global</span>
        </div>
      </section>
      <Footer/>
    </div>
  );
};
window.GiftCards = GiftCards;
