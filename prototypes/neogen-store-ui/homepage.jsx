// Homepage — NeoGen real brand system
const Homepage = () => {
  const [activeHero, setActiveHero] = React.useState(0);
  const heroProducts = [
    {sku:'GM-GPU-GIG-001', ar:'Gigabyte AORUS RTX 5090 Master', en:'32GB GDDR7 · PCIe 5.0', price:27999, cat:'Hardware', ph:'RTX 5090 GPU'},
    {sku:'NT-MPC-MNF-001', ar:'MinisForum MS-01 خادم هوم لاب', en:'Intel Core Ultra 9 · 2× 10GbE', price:3999, was:4599, cat:'Homelab', ph:'MinisForum MS-01'},
    {sku:'SH-HUB-HASS-001', ar:'Home Assistant Green', en:'Smart Home Hub · ARM · 4GB', price:890, cat:'Smart Home', ph:'Home Assistant'},
    {sku:'NG-ENT-003', ar:'Ubiquiti UniFi Dream Machine Pro', en:'All-in-One · 1U · 10G SFP+', price:2054, was:2570, cat:'Networking', ph:'UDM-Pro'},
    {sku:'GM-MON-ASU-001', ar:'ASUS ROG Swift OLED PG27AQDM', en:'27" QHD · 240Hz · OLED', price:4080, cat:'Gaming', ph:'ROG OLED Monitor'},
  ];
  const current = heroProducts[activeHero];

  React.useEffect(() => {
    const t = setInterval(() => setActiveHero(a => (a+1) % heroProducts.length), 4000);
    return () => clearInterval(t);
  }, []);

  return (
    <div dir="rtl" style={{background:'var(--bg)', color:'var(--ink)', minHeight:'100%', fontFamily:'var(--f-ar)'}}>
      <TopBar/>
      <Header/>

      {/* HERO */}
      <section style={{
        borderBottom:'1px solid var(--rule)', overflow:'hidden', position:'relative',
        background:`
          radial-gradient(900px circle at 70% 40%, rgba(56,189,248,0.08), transparent 55%),
          linear-gradient(rgba(10,10,10,0.035) 1px, transparent 1px),
          linear-gradient(90deg, rgba(10,10,10,0.035) 1px, transparent 1px),
          #F8FAFC`,
        backgroundSize:'auto, 48px 48px, 48px 48px, auto'
      }}>
        <div style={{maxWidth:1440, margin:'0 auto', padding:'88px 48px 72px'}}>
          <div style={{display:'grid', gridTemplateColumns:'1.1fr 1fr', gap:80, alignItems:'center'}}>
            <div>
              <div style={{
                display:'inline-flex', alignItems:'center', gap:8, marginBottom:28,
                padding:'6px 14px', border:'1px solid var(--rule-strong)', borderRadius:'var(--r-pill)',
                fontFamily:'var(--f-mono)', fontSize:11, textTransform:'uppercase', letterSpacing:'0.08em', color:'var(--ink-4)'
              }}>
                <span className="dot dot-on"></span>
                متجر تقني سعودي · معتمد · 2026
              </div>
              <h1 style={{
                fontFamily:'var(--f-wordmark)', fontWeight:700,
                fontSize:'clamp(52px,6.5vw,96px)', lineHeight:0.92,
                letterSpacing:'-0.025em', margin:'0 0 12px', color:'var(--indigo)'
              }}>
                جيل<br/>
                <span style={{fontFamily:'var(--f-wordmark)', fontStyle:'italic', color:'var(--accent)', fontWeight:400}}>النِّقلة</span><br/>
                التقنية.
              </h1>
              <div style={{width:56, height:3, background:'var(--accent)', borderRadius:2, margin:'28px 0'}}></div>
              <p style={{fontSize:17, lineHeight:1.65, color:'var(--ink-3)', maxWidth:500, margin:0}}>
                وحدات مختارة لمحترفي الشبكات، الهوم لاب، البيوت الذكية، والألعاب.
                مواصفات بدون مبالغة. شحن من المملكة لكل دول الخليج.
              </p>
              <div style={{display:'flex', gap:12, marginTop:36}}>
                <button className="btn" style={{borderRadius:'var(--r-2)'}}>
                  تصفّح المتجر <span style={{fontFamily:'var(--f-wordmark)'}}>→</span>
                </button>
                <button className="btn btn-ghost" style={{borderRadius:'var(--r-2)'}}>ابنِ جهازك</button>
              </div>
              <div style={{display:'flex', gap:10, alignItems:'center', marginTop:36, flexWrap:'wrap'}}>
                <span style={{fontFamily:'var(--f-mono)', fontSize:10, textTransform:'uppercase', letterSpacing:'0.08em', color:'var(--dim)'}}>يشحن إلى:</span>
                {[['🇸🇦','KSA'],['🇦🇪','UAE'],['🇰🇼','KW'],['🇧🇭','BH'],['🇴🇲','OM'],['🇶🇦','QA']].map(([f,c],i)=>(
                  <span key={i} style={{display:'flex', alignItems:'center', gap:4, fontFamily:'var(--f-mono)', fontSize:11, color:'var(--ink-4)'}}>
                    <span style={{fontSize:16}}>{f}</span> {c}
                  </span>
                ))}
              </div>
            </div>

            {/* Product Gallery */}
            <div>
              {/* Main product card */}
              <div style={{
                background:'var(--surface)', border:'1px solid var(--rule)',
                borderRadius:'var(--r-3)', boxShadow:'var(--shadow-lg)',
                overflow:'hidden', position:'relative'
              }}>
                {/* Category badge */}
                <div style={{
                  position:'absolute', top:16, insetInlineStart:16, zIndex:2
                }}>
                  <span className="chip chip-sky" style={{fontSize:10}}>{current.cat}</span>
                </div>
                {/* Main image placeholder */}
                <div className="ph" style={{
                  aspectRatio:'4/3', borderRadius:0, border:'none',
                  borderBottom:'1px solid var(--rule)',
                  background:`repeating-linear-gradient(135deg, #EEF2F6 0 10px, #E2E8F0 10px 20px)`
                }}>
                  <span className="ph-label">{current.ph}</span>
                </div>
                {/* Info */}
                <div style={{padding:'20px 24px'}}>
                  <div className="mono" style={{fontSize:10, color:'var(--dim)', marginBottom:6}}>{current.sku}</div>
                  <h3 style={{margin:'0 0 4px', fontSize:18, fontWeight:700, color:'var(--indigo)', lineHeight:1.2}}>{current.ar}</h3>
                  <div style={{fontFamily:'var(--f-wordmark)', fontSize:12, color:'var(--ink-4)', marginBottom:16}}>{current.en}</div>
                  <div style={{display:'flex', justifyContent:'space-between', alignItems:'center'}}>
                    <div className="price">
                      <span className="price-now" style={{fontSize:24}}>{current.price.toLocaleString('en-US')}</span>
                      <span className="price-sar">SAR</span>
                      {current.was && <span className="price-was">{current.was.toLocaleString('en-US')}</span>}
                    </div>
                    <button className="btn btn-sm" style={{borderRadius:'var(--r-1)'}}>أضف للسلة +</button>
                  </div>
                </div>
                {/* Progress bar */}
                <div style={{height:2, background:'var(--surface-2)'}}>
                  <div style={{
                    height:'100%', background:'var(--accent)',
                    width:`${((activeHero+1)/heroProducts.length)*100}%`,
                    transition:'width 0.4s var(--ease)'
                  }}></div>
                </div>
              </div>

              {/* Thumbnail strip */}
              <div style={{display:'grid', gridTemplateColumns:'repeat(5,1fr)', gap:8, marginTop:10}}>
                {heroProducts.map((p,i)=>(
                  <button key={i} onClick={()=>setActiveHero(i)} style={{
                    padding:0, border:'2px solid '+(activeHero===i?'var(--accent)':'var(--rule)'),
                    borderRadius:'var(--r-1)', overflow:'hidden', cursor:'pointer',
                    background:'none', transition:'border-color .2s'
                  }}>
                    <div className="ph" style={{
                      aspectRatio:'1/1', borderRadius:0, border:'none',
                      background: activeHero===i
                        ? `repeating-linear-gradient(135deg, #BAE6FD 0 6px, #E0F2FE 6px 12px)`
                        : `repeating-linear-gradient(135deg, #EEF2F6 0 6px, #E2E8F0 6px 12px)`
                    }}>
                      <span className="ph-label" style={{fontSize:7, padding:'2px 4px'}}>{p.ph.split(' ')[0]}</span>
                    </div>
                  </button>
                ))}
              </div>
              {/* Dot nav */}
              <div style={{display:'flex', justifyContent:'center', gap:6, marginTop:12}}>
                {heroProducts.map((_,i)=>(
                  <button key={i} onClick={()=>setActiveHero(i)} style={{
                    width: activeHero===i?20:6, height:6,
                    borderRadius:3, border:'none', cursor:'pointer',
                    background: activeHero===i?'var(--accent)':'var(--rule-strong)',
                    transition:'width .3s var(--ease), background .2s'
                  }}></button>
                ))}
              </div>
            </div>
          </div>

          {/* Trust strip */}
          <div style={{
            display:'grid', gridTemplateColumns:'repeat(5,1fr)',
            marginTop:72, border:'1px solid var(--rule)',
            borderRadius:'var(--r-2)', background:'var(--surface)',
            boxShadow:'var(--shadow-sm)', overflow:'hidden'
          }}>
            {[['السجل التجاري','7053130576'],['الضريبة','15% شاملة'],['الشحن','2–5 أيام عمل'],['الإرجاع','14 يوم'],['الضمان','12 شهر']].map(([k,v],i)=>(
              <div key={i} style={{
                padding:'18px 20px',
                borderInlineStart: i?'1px solid var(--rule)':'none',
                display:'flex', flexDirection:'column', gap:4
              }}>
                <span className="mono-up" style={{color:'var(--dim)', fontSize:9}}>{k}</span>
                <span style={{fontWeight:600, fontSize:14, color:'var(--indigo)'}}>{v}</span>
              </div>
            ))}
          </div>
        </div>
      </section>

    {/* CATEGORIES */}
    <section style={{maxWidth:1440, margin:'0 auto', padding:'96px 48px'}}>
      <SectionHeader n={1} eyebrow="الفئات · CATEGORIES" title="ست فئات. لعمل تقني واضح." subtitle="كل فئة مهيّأة لنوع مشغّل محدّد. كل وحدة اختبرناها قبل الإضافة." action="كل الفئات"/>
      <div style={{display:'grid', gridTemplateColumns:'repeat(3,1fr)', gap:16}}>
        {[
          {n:83, ar:'بطاقات رقمية', en:'Gift Cards & Keys', desc:'تفعيل فوري · Apple · Steam · PSN · GCC',  accent:true},
          {n:51, ar:'البيت الذكي', en:'Smart Home', desc:'Aqara · Shelly · Home Assistant · Matter', accent:false},
          {n:39, ar:'الألعاب', en:'Gaming', desc:'يدّات، شاشات، صوت، بث مباشر', accent:false},
          {n:29, ar:'هوم لاب', en:'Homelab', desc:'رفوف، سيرفرات، NAS، Proxmox', accent:false},
          {n:26, ar:'الشبكات', en:'Networking', desc:'Ubiquiti · TP-Link Omada · MikroTik', accent:false},
          {n:12, ar:'أجهزة PC', en:'Hardware', desc:'GPU، معالجات، ذاكرة، تخزين', accent:false},
        ].map((c,i)=>(
          <a href="#" key={i} style={{
            padding:28, borderRadius:'var(--r-2)',
            border: c.accent ? '1px solid var(--accent-deep)' : '1px solid var(--rule)',
            background: c.accent ? `linear-gradient(135deg, var(--accent-wash), var(--surface))` : 'var(--surface)',
            display:'flex', flexDirection:'column', gap:14, textDecoration:'none', color:'var(--ink)',
            boxShadow:'var(--shadow-sm)', transition:'box-shadow .2s, transform .2s',
            minHeight:200
          }}>
            <div style={{display:'flex', justifyContent:'space-between', alignItems:'flex-start'}}>
              <span className="mono-up" style={{color:'var(--dim)', fontSize:9}}>{String(i+1).padStart(2,'0')} / {c.en}</span>
              <span className={`chip ${c.accent?'chip-sky':''}`} style={{fontSize:10}}>{c.n} منتج</span>
            </div>
            <div className="ph" style={{height:72, borderRadius:'var(--r-1)'}}>
              <span className="ph-label" style={{fontSize:9}}>{c.en.toLowerCase()}</span>
            </div>
            <div>
              <h3 style={{margin:0, fontSize:20, fontWeight:700, color:'var(--indigo)'}}>{c.ar}</h3>
              <p style={{margin:'5px 0 0', fontSize:13, color:'var(--ink-4)'}}>{c.desc}</p>
            </div>
          </a>
        ))}
      </div>
    </section>

    {/* CURATED PICKS */}
    <section style={{borderTop:'1px solid var(--rule)', background:'var(--surface-2)'}}>
      <div style={{maxWidth:1440, margin:'0 auto', padding:'96px 48px'}}>
        <SectionHeader n={2} eyebrow="المختارات · CURATED" title="متوفّرة. بمواصفات. جاهزة للشحن." subtitle="مختارات المشغّلين — اخترناها للموثوقية، قابلية الإصلاح، وتوفّر القطع داخل المملكة." action="كل المختارات"/>
        <div style={{display:'grid', gridTemplateColumns:'repeat(4,1fr)', gap:16}}>
          {SAMPLE_PRODUCTS.slice(0,4).map((p,i)=><ProductCard key={i} {...p}/>)}
        </div>
      </div>
    </section>

    {/* BUILD SERVICE */}
    <section style={{
      background:`linear-gradient(135deg, var(--indigo-deep) 0%, var(--indigo) 100%)`,
      color:'#fff', position:'relative', overflow:'hidden'
    }}>
      <div style={{
        position:'absolute', inset:0, opacity:0.06,
        background:'linear-gradient(rgba(10,10,10,0.15) 1px, transparent 1px), linear-gradient(90deg, rgba(10,10,10,0.15) 1px, transparent 1px)',
        backgroundSize:'48px 48px'
      }}></div>
      <div style={{maxWidth:1440, margin:'0 auto', padding:'96px 48px', position:'relative'}}>
        <div style={{display:'grid', gridTemplateColumns:'1fr 1.5fr', gap:80, alignItems:'start'}}>
          <div>
            <div className="section-mark" style={{marginBottom:24, color:'rgba(255,255,255,0.3)'}}>
              <span>03</span><span style={{color:'rgba(255,255,255,0.7)'}}>· مكتب الخدمة</span>
            </div>
            <h2 className="t-h1" style={{margin:0, color:'#fff', textWrap:'balance'}}>
              تنفيذ مخصّص.<br/>
              <span style={{color:'var(--accent)'}}>من الفكرة<br/>إلى التشغيل.</span>
            </h2>
            <p style={{fontSize:16, color:'rgba(255,255,255,0.6)', marginTop:20, maxWidth:360, lineHeight:1.65}}>
              شبكة، هوم لاب، أو بيت ذكي — نختار المكوّنات، نشحن داخل المملكة، ونركّب بالموقع.
            </p>
            <div style={{display:'flex', gap:12, marginTop:32}}>
              <button className="btn" style={{borderRadius:'var(--r-2)'}}>أرسل المواصفات</button>
              <button className="btn btn-ghost" style={{borderColor:'rgba(255,255,255,0.25)', color:'#fff', borderRadius:'var(--r-2)'}}>واتساب</button>
            </div>
          </div>
          <div style={{display:'grid', gridTemplateColumns:'1fr 1fr 1fr', gap:0, border:'1px solid rgba(255,255,255,0.1)', borderRadius:'var(--r-3)', overflow:'hidden'}}>
            {[
              {n:'01', ar:'شبكة مكتبية', en:'Office Network', items:['تخطيط وتركيب كامل','MikroTik · Ubiquiti','دعم بعد التشغيل']},
              {n:'02', ar:'هوم لاب', en:'Homelab', items:['اختيار المكوّنات','MinisForum · Synology','Proxmox / TrueNAS']},
              {n:'03', ar:'بيت ذكي', en:'Smart Home', items:['تخطيط لكل غرفة','Aqara · SwitchBot','HomeKit · Matter']},
            ].map((s,i)=>(
              <div key={i} style={{
                padding:28,
                borderInlineEnd: i<2?'1px solid rgba(255,255,255,0.1)':'none',
                display:'flex', flexDirection:'column', gap:20
              }}>
                <div style={{display:'flex', justifyContent:'space-between'}}>
                  <span className="mono-up" style={{color:'rgba(255,255,255,0.35)'}}>{s.n}</span>
                  <span className="dot dot-on"></span>
                </div>
                <div>
                  <h3 style={{margin:0, fontSize:20, fontWeight:700, color:'#fff'}}>{s.ar}</h3>
                  <span style={{fontFamily:'var(--f-wordmark)', fontSize:11, color:'var(--accent)', display:'block', marginTop:4}}>{s.en}</span>
                </div>
                <ul style={{listStyle:'none', padding:0, margin:0, display:'flex', flexDirection:'column', gap:8}}>
                  {s.items.map((it,j)=>(
                    <li key={j} style={{fontSize:13, color:'rgba(255,255,255,0.55)', display:'flex', gap:8}}>
                      <span style={{color:'var(--accent)', fontFamily:'var(--f-mono)', fontSize:10}}>{String(j+1).padStart(2,'0')}</span>{it}
                    </li>
                  ))}
                </ul>
                <a href="#" style={{color:'var(--accent)', fontSize:13, fontFamily:'var(--f-wordmark)', fontWeight:600}}>اطلب عرضًا →</a>
              </div>
            ))}
          </div>
        </div>
      </div>
    </section>

    {/* NEW ARRIVALS */}
    <section style={{maxWidth:1440, margin:'0 auto', padding:'96px 48px'}}>
      <SectionHeader n={4} eyebrow="وصلت حديثاً · NEW" title="آخر الإضافات إلى الكتالوج." subtitle="منتجات أُضيفت مؤخرًا — اختبرناها قبل الإضافة." action="كل الجديد"/>
      <div style={{display:'grid', gridTemplateColumns:'repeat(4,1fr)', gap:16}}>
        {SAMPLE_PRODUCTS.slice(4,8).map((p,i)=><ProductCard key={i} {...p}/>)}
      </div>
    </section>

    {/* BRANDS */}
    <section style={{borderTop:'1px solid var(--rule)', background:'var(--surface)'}}>
      <div style={{maxWidth:1440, margin:'0 auto', padding:'56px 48px'}}>
        <div className="section-mark" style={{marginBottom:28}}><span>05</span><span style={{color:'var(--ink-3)'}}>· علامات موثّقة · TRUSTED BRANDS</span></div>
        <div style={{display:'grid', gridTemplateColumns:'repeat(8,1fr)', gap:0, border:'1px solid var(--rule)', borderRadius:'var(--r-2)', overflow:'hidden'}}>
          {['ASUS ROG','Ubiquiti','MinisForum','Hubitat','TP-Link','NZXT','Corsair','Gigabyte','Beelink','HyperX','Elgato','DJI','Roborock','SwitchBot','Apple','Adobe'].map((b,i)=>(
            <div key={i} style={{
              padding:'20px 12px',
              borderInlineEnd: (i%8<7)?'1px solid var(--rule)':'none',
              borderBottom: i<8?'1px solid var(--rule)':'none',
              display:'grid', placeItems:'center'
            }}>
              <span style={{fontFamily:'var(--f-wordmark)', fontWeight:600, fontSize:12, color:'var(--ink-3)', textAlign:'center'}}>{b}</span>
            </div>
          ))}
        </div>
      </div>
    </section>

    <Footer/>
  </div>
  );
};
window.Homepage = Homepage;
