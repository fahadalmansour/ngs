// Product Comparison Page
const Comparison = () => {
  const products = [
    {
      sku:'NG-ENT-003', name:'Ubiquiti UniFi Dream Machine Pro', short:'UDM-Pro', brand:'Ubiquiti',
      price:2054, was:2570, ph:'UDM-Pro', rating:4.8, reviews:24,
      specs:{
        'CPU':'Quad-Core ARM @ 1.7GHz','RAM':'4GB DDR4','Storage':'HDD SATA 3.5"',
        'WAN Ports':'1× 10G SFP+','LAN Ports':'8× GbE RJ45','Throughput':'3.5 Gbps IDS/IPS',
        'VPN':'WireGuard · OpenVPN · IPsec','NVR':'نعم · 4 كاميرات','Form':'1U Rack-mount',
        'Power':'50W','Warranty':'12 شهر','PoE':'لا'
      },
      pros:['إدارة موحّدة UniFi','NVR مدمج','10G SFP+','أفضل للمؤسسات'],
      cons:['لا يدعم PoE','سعر مرتفع','يحتاج خبرة إعداد']
    },
    {
      sku:'NT-FWL-NGT-002', name:'Netgate 2100 MAX pfSense+', short:'Netgate 2100', brand:'Netgate',
      price:2191, ph:'Netgate 2100', rating:4.6, reviews:11,
      specs:{
        'CPU':'ARM Cortex-A53 × 4','RAM':'4GB DDR4','Storage':'8GB eMMC',
        'WAN Ports':'1× GbE','LAN Ports':'4× GbE RJ45','Throughput':'2.5 Gbps',
        'VPN':'OpenVPN · WireGuard · IPsec','NVR':'لا','Form':'Desktop',
        'Power':'18W','Warranty':'12 شهر','PoE':'لا'
      },
      pros:['pfSense+ مرن','استهلاك طاقة منخفض','مجتمع ضخم','مفتوح المصدر'],
      cons:['بدون NVR','واجهة أقل سهولة','مقاعد WAN محدودة']
    },
    {
      sku:'NG-ENT-004', name:'UniFi Cloud Gateway Ultra', short:'UCG Ultra', brand:'Ubiquiti',
      price:540, ph:'UCG Ultra', rating:4.7, reviews:18,
      specs:{
        'CPU':'Quad-Core ARM','RAM':'2GB DDR4','Storage':'16GB eMMC',
        'WAN Ports':'1× 2.5G','LAN Ports':'4× GbE RJ45','Throughput','1.5 Gbps',
        'VPN':'WireGuard · OpenVPN','NVR':'نعم · 2 كاميرات','Form':'Desktop Compact',
        'Power':'12W','Warranty':'12 شهر','PoE':'لا'
      },
      pros:['سعر مناسب','صغير الحجم','UniFi متكامل','مثالي للمنزل'],
      cons:['أداء أقل','2 كاميرة فقط','لا 10G']
    },
  ];

  const allSpecs = [...new Set(products.flatMap(p=>Object.keys(p.specs)))];
  const [winner, setWinner] = React.useState(null);

  return (
    <div dir="rtl" style={{background:'var(--bg)', minHeight:'100%', fontFamily:'var(--f-ar)'}}>
      <TopBar/><Header/>
      <div style={{maxWidth:1440, margin:'0 auto', padding:'48px 48px 96px'}}>
        <div style={{display:'flex', justifyContent:'space-between', alignItems:'flex-end', marginBottom:32}}>
          <div>
            <div className="section-mark" style={{marginBottom:12}}><span>00</span><span style={{color:'var(--ink)'}}>· مقارنة المنتجات · COMPARE</span></div>
            <h1 className="t-h1" style={{margin:0, color:'var(--indigo)'}}>مقارنة المنتجات</h1>
            <p className="t-body" style={{margin:'8px 0 0'}}>راوترات الشبكات الاحترافية — 3 منتجات جنباً إلى جنب</p>
          </div>
          <div style={{display:'flex', gap:8}}>
            <button className="btn btn-ghost btn-sm" style={{borderRadius:'var(--r-2)'}}>+ أضف منتجاً</button>
            <button className="btn btn-ghost btn-sm" style={{borderRadius:'var(--r-2)'}}>مشاركة المقارنة</button>
          </div>
        </div>

        {/* Product header row */}
        <div style={{display:'grid', gridTemplateColumns:'220px repeat(3,1fr)', gap:0, border:'1px solid var(--rule)', borderRadius:'var(--r-2)', overflow:'hidden', marginBottom:2, background:'var(--surface)'}}>
          <div style={{padding:20, borderInlineEnd:'1px solid var(--rule)', background:'var(--surface-2)', display:'flex', alignItems:'center'}}>
            <span className="mono-up" style={{color:'var(--dim)', fontSize:9}}>المنتج</span>
          </div>
          {products.map((p,i)=>(
            <div key={i} style={{
              padding:20, borderInlineEnd: i<2?'1px solid var(--rule)':'none',
              display:'flex', flexDirection:'column', gap:10,
              background: winner===i ? 'var(--accent-wash)' : 'var(--surface)',
              position:'relative'
            }}>
              {winner===i && <div style={{position:'absolute', top:0, insetInlineStart:0, insetInlineEnd:0, height:3, background:'var(--accent)'}}/>}
              <div className="ph" style={{aspectRatio:'4/3', borderRadius:'var(--r-1)'}}>
                <span className="ph-label" style={{fontSize:9}}>{p.ph}</span>
              </div>
              <div>
                <div className="mono" style={{fontSize:10, color:'var(--dim)'}}>{p.sku} · {p.brand}</div>
                <div style={{fontWeight:700, fontSize:15, color:'var(--indigo)', lineHeight:1.3, marginTop:4}}>{p.name}</div>
                <div style={{display:'flex', gap:4, alignItems:'center', marginTop:6}}>
                  <div style={{display:'flex', gap:1}}>{[1,2,3,4,5].map(s=><span key={s} style={{fontSize:11, color:s<=Math.round(p.rating)?'#F59E0B':'var(--rule-strong)'}}>★</span>)}</div>
                  <span className="mono" style={{fontSize:10, color:'var(--dim)'}}>{p.rating} ({p.reviews})</span>
                </div>
              </div>
              <div className="price">
                <span className="price-now" style={{fontSize:20}}>{p.price.toLocaleString('en-US')}</span>
                <span className="price-sar">SAR</span>
                {p.was && <span className="price-was">{p.was.toLocaleString('en-US')}</span>}
              </div>
              <div style={{display:'flex', gap:6}}>
                <button className="btn btn-sm" style={{flex:1, justifyContent:'center', borderRadius:'var(--r-1)', fontSize:12}}>+ سلة</button>
                <button onClick={()=>setWinner(winner===i?null:i)} className={`btn btn-sm ${winner===i?'':'btn-ghost'}`} style={{borderRadius:'var(--r-1)', fontSize:11, padding:'8px 10px'}}>
                  {winner===i?'★ الأفضل':'الأفضل؟'}
                </button>
              </div>
            </div>
          ))}
        </div>

        {/* Specs comparison table */}
        <div style={{border:'1px solid var(--rule)', borderRadius:'var(--r-2)', overflow:'hidden', marginBottom:16}}>
          <div style={{display:'grid', gridTemplateColumns:'220px repeat(3,1fr)', background:'var(--indigo)', color:'#fff'}}>
            <div style={{padding:'12px 20px', borderInlineEnd:'1px solid rgba(255,255,255,0.1)'}}>
              <span className="mono-up" style={{color:'rgba(255,255,255,0.5)', fontSize:9}}>المواصفة</span>
            </div>
            {products.map((p,i)=>(
              <div key={i} style={{padding:'12px 20px', borderInlineEnd:i<2?'1px solid rgba(255,255,255,0.1)':'none'}}>
                <span style={{fontFamily:'var(--f-wordmark)', fontWeight:700, fontSize:13}}>{p.short}</span>
              </div>
            ))}
          </div>
          {allSpecs.map((spec,si)=>(
            <div key={si} style={{
              display:'grid', gridTemplateColumns:'220px repeat(3,1fr)',
              background: si%2===0?'var(--surface)':'var(--surface-2)',
              borderTop:'1px solid var(--rule)'
            }}>
              <div style={{padding:'12px 20px', borderInlineEnd:'1px solid var(--rule)'}}>
                <span className="mono-up" style={{color:'var(--ink-4)', fontSize:9}}>{spec}</span>
              </div>
              {products.map((p,pi)=>{
                const val = p.specs[spec] || '—';
                const isYes = val==='نعم'; const isNo = val==='لا';
                return (
                  <div key={pi} style={{
                    padding:'12px 20px', borderInlineEnd:pi<2?'1px solid var(--rule)':'none',
                    background: winner===pi?'rgba(56,189,248,0.04)':'transparent'
                  }}>
                    <span style={{
                      fontSize:13, fontFamily: spec.includes('Power')||spec.includes('RAM')||spec.includes('CPU')?'var(--f-wordmark)':'var(--f-ar)',
                      color: isYes?'var(--good)':isNo?'var(--sale)':'var(--ink-2)',
                      fontWeight: isYes||isNo?700:400
                    }}>{isYes?'✓ نعم':isNo?'✗ لا':val}</span>
                  </div>
                );
              })}
            </div>
          ))}
        </div>

        {/* Pros / Cons */}
        <div style={{display:'grid', gridTemplateColumns:'220px repeat(3,1fr)', gap:0, border:'1px solid var(--rule)', borderRadius:'var(--r-2)', overflow:'hidden'}}>
          <div style={{padding:20, borderInlineEnd:'1px solid var(--rule)', background:'var(--surface-2)', display:'flex', alignItems:'flex-start', paddingTop:24}}>
            <span className="mono-up" style={{color:'var(--dim)', fontSize:9}}>الإيجابيات والسلبيات</span>
          </div>
          {products.map((p,i)=>(
            <div key={i} style={{padding:20, borderInlineEnd:i<2?'1px solid var(--rule)':'none', background:winner===i?'var(--accent-wash)':'var(--surface)'}}>
              <div style={{marginBottom:14}}>
                <div className="mono-up" style={{color:'var(--good)', fontSize:9, marginBottom:8}}>الإيجابيات</div>
                {p.pros.map((pr,j)=>(
                  <div key={j} style={{display:'flex', gap:8, alignItems:'flex-start', marginBottom:6}}>
                    <span style={{color:'var(--good)', fontSize:12, flexShrink:0}}>✓</span>
                    <span style={{fontSize:13, color:'var(--ink-2)', lineHeight:1.4}}>{pr}</span>
                  </div>
                ))}
              </div>
              <div>
                <div className="mono-up" style={{color:'var(--sale)', fontSize:9, marginBottom:8}}>السلبيات</div>
                {p.cons.map((con,j)=>(
                  <div key={j} style={{display:'flex', gap:8, alignItems:'flex-start', marginBottom:6}}>
                    <span style={{color:'var(--sale)', fontSize:12, flexShrink:0}}>✗</span>
                    <span style={{fontSize:13, color:'var(--ink-2)', lineHeight:1.4}}>{con}</span>
                  </div>
                ))}
              </div>
            </div>
          ))}
        </div>
      </div>
      <Footer/>
    </div>
  );
};
window.Comparison = Comparison;
