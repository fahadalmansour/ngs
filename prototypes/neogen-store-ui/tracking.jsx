// Shipment Tracking page
const ShipmentTracking = () => {
  const [trackingNum, setTrackingNum] = React.useState('NG-2026-04721');

  const shipment = {
    order: 'NG-2026-04721',
    carrier: 'Aramex',
    trackingCode: '1234567890AR',
    status: 'في الطريق إليك',
    eta: '3 مايو 2026',
    origin: 'الرياض، المملكة العربية السعودية',
    destination: 'حي الياسمين، الرياض 13325',
    items: [
      {sku:'NG-ENT-003', name:'Ubiquiti UniFi Dream Machine Pro', qty:1, ph:'UDM-Pro'},
      {sku:'NT-WAP-TPL-002', name:'TP-Link Omada EAP650 × 2', qty:2, ph:'EAP650'},
      {sku:'NT-CBL-GEN-001', name:'كابل Cat6a مصفّح 305م', qty:1, ph:'cat6a'},
    ],
    steps: [
      {label:'تم استلام الطلب',       sub:'1 مايو 2026 · 3:42م',   done:true,  active:false, loc:'نيوجن ستور · الرياض'},
      {label:'تم التجهيز والتغليف',   sub:'2 مايو 2026 · 9:15ص',   done:true,  active:false, loc:'مستودع نيوجن · الرياض'},
      {label:'تم التسليم لـ Aramex',  sub:'2 مايو 2026 · 2:30م',   done:true,  active:false, loc:'مركز شحن Aramex · الرياض'},
      {label:'في الطريق إليك',        sub:'3 مايو 2026 · 8:00ص',   done:false, active:true,  loc:'على الطريق — الرياض'},
      {label:'تم التسليم',            sub:'3 مايو 2026 · متوقع',    done:false, active:false, loc:'حي الياسمين'},
    ],
  };

  const mapPoints = [
    {x:15, y:60, label:'المستودع'},
    {x:35, y:45, label:'Aramex'},
    {x:58, y:55, label:'◉ الشحنة'},
    {x:82, y:42, label:'وجهتك'},
  ];

  return (
    <div dir="rtl" style={{background:'var(--bg)', minHeight:'100%', fontFamily:'var(--f-ar)'}}>
      <TopBar/>
      <Header/>

      <div style={{maxWidth:1440, margin:'0 auto', padding:'48px 48px 96px'}}>
        {/* Search bar */}
        <div style={{marginBottom:40}}>
          <div className="section-mark" style={{marginBottom:16}}><span>00</span><span style={{color:'var(--ink)'}}>· تتبع الشحنة · TRACK SHIPMENT</span></div>
          <div style={{display:'flex', gap:12, maxWidth:600}}>
            <div style={{flex:1, display:'flex', alignItems:'center', gap:12, background:'var(--surface)', border:'1px solid var(--rule-strong)', borderRadius:'var(--r-2)', padding:'12px 16px', boxShadow:'var(--shadow-sm)'}}>
              <span style={{fontSize:16, opacity:0.4}}>⌕</span>
              <input value={trackingNum} onChange={e=>setTrackingNum(e.target.value)}
                style={{flex:1, border:'none', outline:'none', background:'transparent', fontFamily:'var(--f-mono)', fontSize:15, color:'var(--ink)', letterSpacing:'0.05em'}}
                placeholder="رقم الطلب أو رمز التتبع..."
              />
            </div>
            <button className="btn" style={{borderRadius:'var(--r-2)', padding:'12px 24px'}}>تتبع</button>
          </div>
        </div>

        <div style={{display:'grid', gridTemplateColumns:'1.3fr 1fr', gap:32}}>
          {/* Left — timeline + details */}
          <div style={{display:'flex', flexDirection:'column', gap:20}}>

            {/* Status hero card */}
            <div style={{
              background:`linear-gradient(135deg, var(--indigo-deep), var(--indigo))`,
              borderRadius:'var(--r-3)', padding:'32px 36px', color:'#fff',
              position:'relative', overflow:'hidden'
            }}>
              <div style={{position:'absolute', inset:0, opacity:0.06, background:'linear-gradient(rgba(255,255,255,0.2) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,0.2) 1px,transparent 1px)', backgroundSize:'48px 48px'}}/>
              <div style={{position:'relative'}}>
                <div style={{display:'flex', justifyContent:'space-between', alignItems:'flex-start', marginBottom:20}}>
                  <div>
                    <div className="mono-up" style={{color:'rgba(255,255,255,0.4)', marginBottom:8}}>رقم الطلب · ORDER</div>
                    <div style={{fontFamily:'var(--f-wordmark)', fontWeight:700, fontSize:20, color:'var(--accent)'}}>{shipment.order}</div>
                  </div>
                  <div style={{textAlign:'end'}}>
                    <div className="mono-up" style={{color:'rgba(255,255,255,0.4)', marginBottom:8}}>شركة الشحن</div>
                    <div style={{fontFamily:'var(--f-wordmark)', fontWeight:700, fontSize:18}}>{shipment.carrier}</div>
                    <div className="mono" style={{fontSize:12, color:'rgba(255,255,255,0.5)', marginTop:3}}>{shipment.trackingCode}</div>
                  </div>
                </div>
                <div style={{display:'flex', alignItems:'center', gap:12, marginBottom:16}}>
                  <div style={{width:12, height:12, borderRadius:'50%', background:'var(--accent)', boxShadow:'0 0 0 4px rgba(56,189,248,0.3)', animation:'pulse 2s infinite'}}></div>
                  <h2 style={{margin:0, fontSize:28, fontWeight:700, color:'#fff'}}>{shipment.status}</h2>
                </div>
                <div style={{display:'flex', gap:32}}>
                  <div>
                    <div className="mono-up" style={{color:'rgba(255,255,255,0.4)', fontSize:9, marginBottom:4}}>موعد التسليم المتوقع</div>
                    <div style={{fontFamily:'var(--f-wordmark)', fontSize:16, fontWeight:600, color:'var(--accent)'}}>{shipment.eta}</div>
                  </div>
                  <div>
                    <div className="mono-up" style={{color:'rgba(255,255,255,0.4)', fontSize:9, marginBottom:4}}>الوجهة</div>
                    <div style={{fontSize:14, color:'rgba(255,255,255,0.8)'}}>{shipment.destination}</div>
                  </div>
                </div>
              </div>
            </div>

            {/* Map placeholder */}
            <div style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', overflow:'hidden', boxShadow:'var(--shadow-sm)'}}>
              <div style={{padding:'14px 20px', borderBottom:'1px solid var(--rule)', display:'flex', justifyContent:'space-between'}}>
                <span className="mono-up" style={{color:'var(--ink-4)'}}>الموقع الحالي · LIVE LOCATION</span>
                <span className="chip chip-sky" style={{fontSize:9}}>● مباشر</span>
              </div>
              {/* SVG map placeholder */}
              <div style={{position:'relative', height:200, background:'#EEF2F6', overflow:'hidden'}}>
                {/* Grid lines */}
                <svg style={{position:'absolute', inset:0, width:'100%', height:'100%'}} viewBox="0 0 100 100" preserveAspectRatio="none">
                  <defs>
                    <pattern id="mapgrid" width="10" height="10" patternUnits="userSpaceOnUse">
                      <path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(148,163,184,0.3)" strokeWidth="0.3"/>
                    </pattern>
                  </defs>
                  <rect width="100" height="100" fill="url(#mapgrid)"/>
                  {/* Route line */}
                  <polyline points="15,60 35,45 58,55 82,42" fill="none" stroke="#38BDF8" strokeWidth="1.5" strokeDasharray="3,2"/>
                  {/* Points */}
                  {mapPoints.map((p,i)=>(
                    <g key={i}>
                      <circle cx={p.x} cy={p.y} r={i===2?3:2} fill={i===2?'#38BDF8':'#1A2B4B'} stroke="white" strokeWidth="0.8"/>
                    </g>
                  ))}
                </svg>
                {/* Labels */}
                {mapPoints.map((p,i)=>(
                  <div key={i} style={{
                    position:'absolute',
                    left:`${p.x}%`, top:`${p.y}%`,
                    transform:'translate(-50%, -120%)',
                    fontFamily:'var(--f-mono)', fontSize:9, textTransform:'uppercase',
                    background: i===2?'var(--accent)':'var(--surface)',
                    color: i===2?'var(--indigo)':'var(--ink-3)',
                    padding:'2px 6px', borderRadius:2, whiteSpace:'nowrap',
                    boxShadow:'var(--shadow-sm)', border:'1px solid var(--rule)'
                  }}>{p.label}</div>
                ))}
                <div style={{
                  position:'absolute', bottom:8, insetInlineEnd:8,
                  fontFamily:'var(--f-mono)', fontSize:9, color:'var(--dim)',
                  background:'rgba(255,255,255,0.85)', padding:'3px 8px', borderRadius:2,
                  border:'1px solid var(--rule)'
                }}>🗺 placeholder · خريطة تفاعلية تُدمج هنا</div>
              </div>
            </div>

            {/* Timeline */}
            <div style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', padding:'24px', boxShadow:'var(--shadow-sm)'}}>
              <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:20}}>مسار الشحنة · JOURNEY</div>
              <div style={{display:'flex', flexDirection:'column', gap:0}}>
                {shipment.steps.map((step,i,arr)=>(
                  <div key={i} style={{display:'grid', gridTemplateColumns:'32px 1fr', gap:14}}>
                    <div style={{display:'flex', flexDirection:'column', alignItems:'center'}}>
                      <div style={{
                        width:30, height:30, borderRadius:'50%', flexShrink:0,
                        background: step.done?'var(--good)':step.active?'var(--accent)':'var(--surface-2)',
                        border:`2px solid ${step.done?'var(--good)':step.active?'var(--accent)':'var(--rule)'}`,
                        display:'grid', placeItems:'center', fontSize:13, color:'#fff', fontWeight:700
                      }}>{step.done?'✓':step.active?'◉':''}</div>
                      {i<arr.length-1 && <div style={{width:2, flex:1, minHeight:20, background:step.done?'var(--good)':'var(--rule)', margin:'4px 0'}}></div>}
                    </div>
                    <div style={{paddingBottom: i<arr.length-1?20:0}}>
                      <div style={{fontWeight:600, fontSize:14, color:step.active?'var(--accent-ink)':step.done?'var(--ink)':'var(--dim)'}}>{step.label}</div>
                      <div className="mono" style={{fontSize:11, color:'var(--dim)', marginTop:2}}>{step.sub}</div>
                      <div style={{fontSize:12, color:'var(--ink-4)', marginTop:3}}>{step.loc}</div>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </div>

          {/* Right — order items + actions */}
          <div style={{display:'flex', flexDirection:'column', gap:16}}>

            {/* Items */}
            <div style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', overflow:'hidden', boxShadow:'var(--shadow-sm)'}}>
              <div style={{padding:'14px 20px', borderBottom:'1px solid var(--rule)', background:'var(--surface-2)', display:'flex', justifyContent:'space-between'}}>
                <span className="mono-up" style={{color:'var(--ink-4)'}}>محتويات الشحنة</span>
                <span className="mono-up" style={{color:'var(--dim)', fontSize:9}}>{shipment.items.length} منتجات</span>
              </div>
              {shipment.items.map((it,i)=>(
                <div key={i} style={{display:'grid', gridTemplateColumns:'56px 1fr', gap:0, borderBottom:i<shipment.items.length-1?'1px solid var(--rule)':'none'}}>
                  <div className="ph" style={{height:56, borderRadius:0, border:'none', borderInlineEnd:'1px solid var(--rule)'}}>
                    <span className="ph-label" style={{fontSize:7}}>{it.ph}</span>
                  </div>
                  <div style={{padding:'10px 14px'}}>
                    <div className="mono" style={{fontSize:9, color:'var(--dim)'}}>{it.sku}</div>
                    <div style={{fontWeight:600, fontSize:13, color:'var(--indigo)', marginTop:2}}>{it.name}</div>
                  </div>
                </div>
              ))}
            </div>

            {/* Quick actions */}
            <div style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', padding:20, boxShadow:'var(--shadow-sm)'}}>
              <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:14}}>إجراءات الطلب · ORDER ACTIONS</div>
              <div style={{display:'flex', flexDirection:'column', gap:8}}>
                {/* Return */}
                <button style={{
                  display:'flex', alignItems:'center', gap:12, padding:'14px 16px',
                  border:'1px solid var(--rule)', borderRadius:'var(--r-2)',
                  background:'var(--surface)', cursor:'pointer', textAlign:'start', width:'100%'
                }}>
                  <span style={{fontSize:22}}>🔄</span>
                  <div style={{flex:1}}>
                    <div style={{fontWeight:600, fontSize:14, color:'var(--indigo)'}}>طلب إرجاع</div>
                    <div className="mono" style={{fontSize:10, color:'var(--dim)', marginTop:2}}>خلال 14 يوم من الاستلام</div>
                  </div>
                  <span style={{color:'var(--accent-deep)', fontSize:14}}>→</span>
                </button>
                {/* Replace */}
                <button style={{
                  display:'flex', alignItems:'center', gap:12, padding:'14px 16px',
                  border:'1px solid var(--rule)', borderRadius:'var(--r-2)',
                  background:'var(--surface)', cursor:'pointer', textAlign:'start', width:'100%'
                }}>
                  <span style={{fontSize:22}}>🔁</span>
                  <div style={{flex:1}}>
                    <div style={{fontWeight:600, fontSize:14, color:'var(--indigo)'}}>طلب استبدال</div>
                    <div className="mono" style={{fontSize:10, color:'var(--dim)', marginTop:2}}>استبدال منتج معيب أو تالف</div>
                  </div>
                  <span style={{color:'var(--accent-deep)', fontSize:14}}>→</span>
                </button>
                {/* Cancel */}
                <button style={{
                  display:'flex', alignItems:'center', gap:12, padding:'14px 16px',
                  border:'1px solid var(--sale-soft)', borderRadius:'var(--r-2)',
                  background:'var(--sale-soft)', cursor:'pointer', textAlign:'start', width:'100%'
                }}>
                  <span style={{fontSize:22}}>❌</span>
                  <div style={{flex:1}}>
                    <div style={{fontWeight:600, fontSize:14, color:'var(--sale)'}}>إلغاء الطلب</div>
                    <div className="mono" style={{fontSize:10, color:'var(--sale)', opacity:0.7, marginTop:2}}>متاح قبل الشحن فقط</div>
                  </div>
                  <span style={{color:'var(--sale)', fontSize:14}}>→</span>
                </button>
                {/* Warranty claim */}
                <button style={{
                  display:'flex', alignItems:'center', gap:12, padding:'14px 16px',
                  border:'1px solid var(--rule)', borderRadius:'var(--r-2)',
                  background:'var(--surface)', cursor:'pointer', textAlign:'start', width:'100%'
                }}>
                  <span style={{fontSize:22}}>🛡️</span>
                  <div style={{flex:1}}>
                    <div style={{fontWeight:600, fontSize:14, color:'var(--indigo)'}}>طلب صيانة / ضمان</div>
                    <div className="mono" style={{fontSize:10, color:'var(--dim)', marginTop:2}}>ضمان 12 شهر على كل المنتجات</div>
                  </div>
                  <span style={{color:'var(--accent-deep)', fontSize:14}}>→</span>
                </button>
                {/* Contact */}
                <button style={{
                  display:'flex', alignItems:'center', gap:12, padding:'14px 16px',
                  border:'1px solid var(--rule)', borderRadius:'var(--r-2)',
                  background:'var(--surface)', cursor:'pointer', textAlign:'start', width:'100%'
                }}>
                  <span style={{fontSize:22}}>💬</span>
                  <div style={{flex:1}}>
                    <div style={{fontWeight:600, fontSize:14, color:'var(--indigo)'}}>التواصل مع الدعم</div>
                    <div className="mono" style={{fontSize:10, color:'var(--dim)', marginTop:2}}>رد خلال أقل من ساعة</div>
                  </div>
                  <span style={{color:'var(--accent-deep)', fontSize:14}}>→</span>
                </button>
              </div>
            </div>

            {/* Notifications opt-in */}
            <div style={{background:'var(--accent-wash)', border:'1px solid var(--accent-soft)', borderRadius:'var(--r-2)', padding:'16px 20px', display:'flex', justifyContent:'space-between', alignItems:'center'}}>
              <div>
                <div style={{fontWeight:600, fontSize:14, color:'var(--indigo)', marginBottom:4}}>تنبيهات التسليم</div>
                <div style={{fontSize:12, color:'var(--ink-3)'}}>استقبل تحديثات فورية على واتساب أو SMS</div>
              </div>
              <button className="btn btn-sm" style={{borderRadius:'var(--r-1)', flexShrink:0}}>تفعيل</button>
            </div>
          </div>
        </div>
      </div>
      <Footer/>
    </div>
  );
};
window.ShipmentTracking = ShipmentTracking;
