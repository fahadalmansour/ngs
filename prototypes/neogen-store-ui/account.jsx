// User Account page
const Account = () => {
  const [activeTab, setActiveTab] = React.useState(2); // default: Gift Card codes

  const tabs = ['الطلبات', 'المفضلة', 'بطاقاتي · الأكواد', 'الضمانات', 'العناوين', 'طرق الدفع', 'الإعدادات'];

  const orders = [
    {id:'NG-2026-04721', date:'1 مايو 2026', status:'تم الشحن', statusColor:'var(--accent-deep)', items:3, total:5080},
    {id:'NG-2026-03891', date:'15 أبريل 2026', status:'تم التسليم', statusColor:'var(--good)', items:1, total:890},
    {id:'NG-2026-02340', date:'2 مارس 2026', status:'تم التسليم', statusColor:'var(--good)', items:2, total:1648},
    {id:'NG-2026-01122', date:'14 فبراير 2026', status:'تم التسليم', statusColor:'var(--good)', items:4, total:3299},
  ];

  const wishlist = SAMPLE_PRODUCTS.slice(0, 4);

  const giftCards = [
    {sku:'GC-PSP-KSA-12', name:'PlayStation Plus 12 Months (KSA)', brand:'PlayStation', region:'🇸🇦 KSA', code:'XXXX-XXXX-XXXX-4721', purchased:'1 مايو 2026', status:'مُفعَّل', statusColor:'var(--good)', expires:'1 مايو 2027'},
    {sku:'GC-APL-US-100',  name:'Apple Gift Card $100 (US)',        brand:'Apple',       region:'🇺🇸 US',  code:'XKZP-MNQR-WTVY-8832', purchased:'15 أبريل 2026', status:'غير مُفعَّل', statusColor:'var(--warn)', expires:'—'},
    {sku:'GC-SPF-60-GLB',  name:'Spotify Premium $60 (Global)',     brand:'Spotify',     region:'🌐 GLB', code:'SP60-GLBL-4891-KXMN', purchased:'2 مارس 2026',   status:'مُفعَّل', statusColor:'var(--good)', expires:'2 مارس 2027'},
    {sku:'GC-STM-100',     name:'Steam Gift Card $100',             brand:'Steam',       region:'🌐 GLB', code:'STM-WQKJ-PXRZ-7741', purchased:'14 فبراير 2026', status:'مستهلك', statusColor:'var(--dim)',  expires:'—'},
  ];

  const warranties = [
    {sku:'NG-ENT-003', name:'Ubiquiti UniFi Dream Machine Pro', brand:'Ubiquiti', purchased:'1 مايو 2026', expires:'1 مايو 2027', daysLeft:365, status:'نشط', order:'NG-2026-04721', ph:'UDM-Pro'},
    {sku:'NT-WAP-TPL-002', name:'TP-Link Omada EAP650', brand:'TP-Link', purchased:'1 مايو 2026', expires:'1 مايو 2027', daysLeft:365, status:'نشط', order:'NG-2026-04721', ph:'EAP650'},
    {sku:'NT-CBL-GEN-001', name:'كابل Cat6a مصفّح 305م', brand:'Generic', purchased:'1 مايو 2026', expires:'1 مايو 2027', daysLeft:365, status:'نشط', order:'NG-2026-04721', ph:'cat6a'},
    {sku:'SH-HUB-HASS-001', name:'Home Assistant Green', brand:'Nabu Casa', purchased:'15 أبريل 2026', expires:'15 أبريل 2027', daysLeft:349, status:'نشط', order:'NG-2026-03891', ph:'HA Green'},
    {sku:'GM-STR-ELG-001', name:'Elgato Stream Deck MK.2', brand:'Elgato', purchased:'2 مارس 2026', expires:'2 مارس 2027', daysLeft:305, status:'نشط', order:'NG-2026-02340', ph:'Stream Deck'},
    {sku:'GM-PGA-JSA-001', name:'JSAUX 6-in-1 Dock', brand:'JSAUX', purchased:'14 فبراير 2026', expires:'14 فبراير 2027', daysLeft:289, status:'نشط', order:'NG-2026-01122', ph:'USB-C dock'},
  ];

  const addresses = [
    {label:'المنزل', name:'أحمد السبيعي', line1:'شارع الأمير محمد بن عبدالعزيز', line2:'حي الياسمين، الرياض 13325', country:'🇸🇦 السعودية', default:true},
    {label:'العمل', name:'أحمد السبيعي', line1:'طريق الملك فهد، برج المملكة', line2:'الرياض 12211', country:'🇸🇦 السعودية', default:false},
  ];

  return (
    <div dir="rtl" style={{background:'var(--bg)', minHeight:'100%', fontFamily:'var(--f-ar)'}}>
      <TopBar/>
      <Header/>

      <div style={{maxWidth:1440, margin:'0 auto', padding:'48px 48px 96px', display:'grid', gridTemplateColumns:'280px 1fr', gap:40}}>

        {/* Sidebar */}
        <aside>
          {/* Profile card */}
          <div style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-3)', padding:24, marginBottom:16, boxShadow:'var(--shadow-sm)'}}>
            <div style={{display:'flex', alignItems:'center', gap:16, marginBottom:20, paddingBottom:20, borderBottom:'1px solid var(--rule)'}}>
              <div style={{
                width:56, height:56, borderRadius:'50%',
                background:`linear-gradient(135deg, var(--indigo), var(--accent))`,
                display:'grid', placeItems:'center',
                fontFamily:'var(--f-wordmark)', fontWeight:700, fontSize:20, color:'#fff'
              }}>أح</div>
              <div>
                <div style={{fontWeight:700, fontSize:16, color:'var(--indigo)'}}>أحمد السبيعي</div>
                <div className="mono" style={{fontSize:11, color:'var(--dim)', marginTop:2}}>ahmad@example.sa</div>
                <div className="chip chip-sky" style={{marginTop:6, fontSize:9}}>عضو منذ 2024</div>
              </div>
            </div>
            {/* Stats */}
            <div style={{display:'grid', gridTemplateColumns:'1fr 1fr 1fr', gap:8}}>
              {[['4','طلبات'],['4','بطاقات'],['6','ضمانات']].map(([n,l],i)=>(
                <div key={i} style={{textAlign:'center', padding:'10px 6px', background:'var(--surface-2)', borderRadius:'var(--r-1)'}}>
                  <div style={{fontFamily:'var(--f-wordmark)', fontWeight:700, fontSize:20, color:'var(--indigo)'}}>{n}</div>
                  <div className="mono-up" style={{color:'var(--dim)', fontSize:9, marginTop:2}}>{l}</div>
                </div>
              ))}
            </div>
          </div>

          {/* Nav */}
          <div style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', overflow:'hidden', boxShadow:'var(--shadow-sm)'}}>
            {tabs.map((t,i)=>(
              <button key={i} onClick={()=>setActiveTab(i)} style={{
                width:'100%', padding:'14px 20px',
                display:'flex', justifyContent:'space-between', alignItems:'center',
                background: activeTab===i ? 'var(--accent-wash)' : 'transparent',
                borderBottom: i<tabs.length-1 ? '1px solid var(--rule)' : 'none',
                fontFamily:'var(--f-ar)', fontSize:14, fontWeight: activeTab===i?600:400,
                color: activeTab===i ? 'var(--accent-ink)' : 'var(--ink-3)',
                cursor:'pointer', textAlign:'right',
                borderInlineStart: activeTab===i ? '3px solid var(--accent)' : '3px solid transparent',
              }}>
                <span>{t}</span>
                <span style={{fontFamily:'var(--f-mono)', fontSize:12, opacity:0.4}}>→</span>
              </button>
            ))}
          </div>

          <button className="btn btn-ghost btn-sm" style={{width:'100%', justifyContent:'center', marginTop:12, borderRadius:'var(--r-2)', color:'var(--sale)'}}>
            تسجيل الخروج
          </button>
        </aside>

        {/* Main content */}
        <main>
          {/* Orders tab */}
          {activeTab === 0 && (
            <div>
              <div style={{display:'flex', justifyContent:'space-between', alignItems:'flex-end', marginBottom:24}}>
                <div>
                  <div className="section-mark" style={{marginBottom:10}}><span>01</span><span style={{color:'var(--ink)'}}>· طلباتي · MY ORDERS</span></div>
                  <h2 className="t-h2" style={{margin:0, color:'var(--indigo)'}}>سجل الطلبات</h2>
                </div>
                <div style={{display:'flex', gap:8}}>
                  {['الكل','جارٍ','مكتمل','ملغي'].map((f,i)=>(
                    <button key={i} className={i===0?'chip chip-solid':'chip'} style={{padding:'6px 12px', cursor:'pointer'}}>{f}</button>
                  ))}
                </div>
              </div>

              <div style={{display:'flex', flexDirection:'column', gap:12}}>
                {orders.map((o,i)=>(
                  <div key={i} style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', boxShadow:'var(--shadow-sm)', overflow:'hidden'}}>
                    <div style={{display:'grid', gridTemplateColumns:'1fr auto auto auto auto', gap:24, padding:'16px 20px', alignItems:'center', borderBottom:'1px solid var(--rule)', background:'var(--surface-2)'}}>
                      <div>
                        <div className="mono" style={{fontSize:11, color:'var(--dim)', marginBottom:3}}>{o.id}</div>
                        <div style={{fontWeight:600, fontSize:14, color:'var(--indigo)'}}>{o.date}</div>
                      </div>
                      <div>
                        <div className="mono-up" style={{color:'var(--dim)', fontSize:9, marginBottom:3}}>المنتجات</div>
                        <div style={{fontWeight:600}}>{o.items} منتجات</div>
                      </div>
                      <div>
                        <div className="mono-up" style={{color:'var(--dim)', fontSize:9, marginBottom:3}}>الإجمالي</div>
                        <div className="price"><span className="price-now" style={{fontSize:16}}>{o.total.toLocaleString('en-US')}</span><span className="price-sar">SAR</span></div>
                      </div>
                      <div>
                        <div className="mono-up" style={{color:'var(--dim)', fontSize:9, marginBottom:3}}>الحالة</div>
                        <span style={{fontFamily:'var(--f-mono)', fontSize:11, color:o.statusColor, textTransform:'uppercase', letterSpacing:'0.06em'}}>● {o.status}</span>
                      </div>
                      <div style={{display:'flex', gap:8}}>
                        <button className="btn btn-ghost btn-sm" style={{borderRadius:'var(--r-1)', fontSize:12}}>التفاصيل</button>
                        {o.status === 'تم التسليم' && <button className="btn btn-sm" style={{borderRadius:'var(--r-1)', fontSize:12}}>إعادة الطلب</button>}
                      </div>
                    </div>
                    {/* Mini items */}
                    <div style={{padding:'12px 20px', display:'flex', gap:10, alignItems:'center'}}>
                      {[...Array(o.items)].map((_,j)=>(
                        <div key={j} className="ph" style={{width:40, height:40, flexShrink:0, borderRadius:'var(--r-1)'}}><span className="ph-label" style={{fontSize:7}}>img</span></div>
                      ))}
                      <span style={{fontSize:12, color:'var(--dim)', marginInlineStart:4}}>+ {o.items} منتجات في هذا الطلب</span>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Wishlist tab */}
          {activeTab === 1 && (
            <div>
              <div className="section-mark" style={{marginBottom:10}}><span>02</span><span style={{color:'var(--ink)'}}>· المفضلة · WISHLIST</span></div>
              <h2 className="t-h2" style={{margin:'0 0 24px', color:'var(--indigo)'}}>المنتجات المحفوظة</h2>
              <div style={{display:'grid', gridTemplateColumns:'repeat(2,1fr)', gap:16}}>
                {wishlist.map((p,i)=><ProductCard key={i} {...p}/>)}
              </div>
            </div>
          )}

          {/* Gift Card Keys tab */}
          {activeTab === 2 && (
            <div>
              <div style={{display:'flex', justifyContent:'space-between', alignItems:'flex-end', marginBottom:24}}>
                <div>
                  <div className="section-mark" style={{marginBottom:10}}><span>03</span><span style={{color:'var(--ink)'}}>· بطاقاتي · MY GIFT CARDS</span></div>
                  <h2 className="t-h2" style={{margin:0, color:'var(--indigo)'}}>أكواد البطاقات الرقمية</h2>
                  <p className="t-body" style={{margin:'8px 0 0'}}>اضغط على الكود لنسخه. احتفظ بأكوادك في مكان آمن.</p>
                </div>
                <div style={{display:'flex', gap:8}}>
                  {['الكل','مُفعَّل','غير مُفعَّل','مستهلك'].map((f,i)=>(
                    <button key={i} className={i===0?'chip chip-solid':'chip'} style={{padding:'6px 12px', cursor:'pointer'}}>{f}</button>
                  ))}
                </div>
              </div>
              <div style={{display:'flex', flexDirection:'column', gap:12}}>
                {giftCards.map((gc,i)=>(
                  <div key={i} style={{
                    background:'var(--surface)', border:'1px solid var(--rule)',
                    borderRadius:'var(--r-2)', overflow:'hidden', boxShadow:'var(--shadow-sm)'
                  }}>
                    <div style={{display:'grid', gridTemplateColumns:'56px 1fr', gap:0}}>
                      {/* Brand color strip */}
                      <div style={{
                        background: gc.brand==='PlayStation'?'#003791': gc.brand==='Apple'?'#1d1d1f': gc.brand==='Spotify'?'#1DB954':'#171a21',
                        display:'grid', placeItems:'center', padding:'16px 8px'
                      }}>
                        <span style={{fontFamily:'var(--f-wordmark)', fontWeight:700, fontSize:9, color:'rgba(255,255,255,0.9)', textAlign:'center', textTransform:'uppercase', letterSpacing:'0.05em', lineHeight:1.3, writingMode:'vertical-rl'}}>{gc.brand}</span>
                      </div>
                      <div style={{padding:'16px 20px'}}>
                        <div style={{display:'flex', justifyContent:'space-between', alignItems:'flex-start', marginBottom:12}}>
                          <div>
                            <div className="mono" style={{fontSize:10, color:'var(--dim)', marginBottom:4}}>{gc.sku} · {gc.region}</div>
                            <div style={{fontWeight:700, fontSize:15, color:'var(--indigo)'}}>{gc.name}</div>
                            <div className="mono-up" style={{fontSize:9, color:'var(--dim)', marginTop:3}}>اشترى: {gc.purchased}</div>
                          </div>
                          <span style={{
                            fontFamily:'var(--f-mono)', fontSize:11, textTransform:'uppercase',
                            letterSpacing:'0.06em', color:gc.statusColor
                          }}>● {gc.status}</span>
                        </div>
                        {/* The key */}
                        <div style={{
                          display:'flex', alignItems:'center', gap:12,
                          background:'var(--surface-2)', border:'1px solid var(--rule)',
                          borderRadius:'var(--r-1)', padding:'12px 16px', cursor:'pointer'
                        }}
                          title="اضغط لنسخ الكود"
                          onClick={()=>navigator.clipboard?.writeText(gc.code)}
                        >
                          <span style={{
                            fontFamily:'var(--f-mono)', fontSize:18, fontWeight:600,
                            letterSpacing:'0.15em', color:'var(--indigo)', flex:1,
                            filter: gc.status==='مستهلك' ? 'blur(4px)' : 'none'
                          }}>{gc.code}</span>
                          <span className="chip chip-sky" style={{fontSize:9, cursor:'pointer', flexShrink:0}}>📋 نسخ</span>
                        </div>
                        {gc.expires !== '—' && (
                          <div className="mono" style={{fontSize:10, color:'var(--dim)', marginTop:8}}>
                            ينتهي: {gc.expires}
                          </div>
                        )}
                      </div>
                    </div>
                  </div>
                ))}
              </div>
              <div style={{
                marginTop:20, padding:'16px 20px',
                border:'1px dashed var(--accent)', borderRadius:'var(--r-2)',
                background:'var(--accent-wash)', display:'flex', gap:12, alignItems:'center'
              }}>
                <span style={{fontSize:20}}>🔒</span>
                <p style={{margin:0, fontSize:13, color:'var(--accent-ink)', lineHeight:1.6}}>
                  أكوادك محفوظة بشكل آمن مشفّر. في حالة فقدان الكود تواصل مع فريق الدعم مع رقم الطلب.
                </p>
              </div>
            </div>
          )}

          {/* Warranties tab */}
          {activeTab === 3 && (
            <div>
              <div style={{display:'flex', justifyContent:'space-between', alignItems:'flex-end', marginBottom:24}}>
                <div>
                  <div className="section-mark" style={{marginBottom:10}}><span>04</span><span style={{color:'var(--ink)'}}>· الضمانات · WARRANTIES</span></div>
                  <h2 className="t-h2" style={{margin:0, color:'var(--indigo)'}}>ضمانات منتجاتك</h2>
                  <p className="t-body" style={{margin:'8px 0 0'}}>{warranties.length} منتجات بضمان نشط · كلها ضمان 12 شهر من نيوجن ستور</p>
                </div>
                <button className="btn btn-sm" style={{borderRadius:'var(--r-2)'}}>طلب خدمة ضمان</button>
              </div>

              {/* Summary strip */}
              <div style={{
                display:'grid', gridTemplateColumns:'repeat(3,1fr)',
                border:'1px solid var(--rule)', borderRadius:'var(--r-2)',
                overflow:'hidden', marginBottom:20, background:'var(--surface)'
              }}>
                {[['6','ضمانات نشطة'],['12 شهر','مدة الضمان لكل منتج'],['0','طلبات صيانة مفتوحة']].map(([n,l],i)=>(
                  <div key={i} style={{padding:'18px 24px', borderInlineEnd: i<2?'1px solid var(--rule)':'none'}}>
                    <div style={{fontFamily:'var(--f-wordmark)', fontSize:28, fontWeight:700, color:'var(--indigo)'}}>{n}</div>
                    <div className="mono-up" style={{color:'var(--dim)', fontSize:9, marginTop:4}}>{l}</div>
                  </div>
                ))}
              </div>

              <div style={{display:'flex', flexDirection:'column', gap:10}}>
                {warranties.map((w,i)=>(
                  <div key={i} style={{
                    background:'var(--surface)', border:'1px solid var(--rule)',
                    borderRadius:'var(--r-2)', display:'grid',
                    gridTemplateColumns:'64px 1fr auto', alignItems:'center',
                    boxShadow:'var(--shadow-sm)', overflow:'hidden'
                  }}>
                    <div className="ph" style={{height:64, borderRadius:0, border:'none', borderInlineEnd:'1px solid var(--rule)'}}>
                      <span className="ph-label" style={{fontSize:7}}>{w.ph}</span>
                    </div>
                    <div style={{padding:'12px 20px'}}>
                      <div style={{display:'flex', gap:8, alignItems:'center', marginBottom:4}}>
                        <span className="mono" style={{fontSize:10, color:'var(--dim)'}}>{w.sku}</span>
                        <span className="mono-up" style={{fontSize:9, color:'var(--dim)'}}>· {w.brand}</span>
                        <span style={{fontFamily:'var(--f-mono)', fontSize:10, color:'var(--good)'}}>● {w.status}</span>
                      </div>
                      <div style={{fontWeight:700, fontSize:14, color:'var(--indigo)', marginBottom:6}}>{w.name}</div>
                      <div style={{display:'flex', gap:20, flexWrap:'wrap'}}>
                        <div>
                          <span className="mono-up" style={{color:'var(--dim)', fontSize:9}}>تاريخ الشراء</span>
                          <div className="mono" style={{fontSize:12, marginTop:2}}>{w.purchased}</div>
                        </div>
                        <div>
                          <span className="mono-up" style={{color:'var(--dim)', fontSize:9}}>ينتهي الضمان</span>
                          <div className="mono" style={{fontSize:12, marginTop:2, color:'var(--indigo)', fontWeight:600}}>{w.expires}</div>
                        </div>
                        <div>
                          <span className="mono-up" style={{color:'var(--dim)', fontSize:9}}>رقم الطلب</span>
                          <div className="mono" style={{fontSize:12, marginTop:2, color:'var(--accent-deep)'}}>{w.order}</div>
                        </div>
                        <div style={{flex:1}}>
                          <span className="mono-up" style={{color:'var(--dim)', fontSize:9}}>المدة المتبقية</span>
                          <div style={{marginTop:4, height:6, background:'var(--surface-2)', borderRadius:3, minWidth:100}}>
                            <div style={{
                              height:'100%', borderRadius:3,
                              background: w.daysLeft > 180 ? 'var(--good)' : w.daysLeft > 60 ? 'var(--warn)' : 'var(--sale)',
                              width: `${Math.round((w.daysLeft/365)*100)}%`
                            }}></div>
                          </div>
                          <div className="mono" style={{fontSize:10, color:'var(--dim)', marginTop:3}}>{w.daysLeft} يوم</div>
                        </div>
                      </div>
                    </div>
                    <div style={{padding:'12px 16px', display:'flex', flexDirection:'column', gap:6}}>
                      <button className="btn btn-sm" style={{borderRadius:'var(--r-1)', fontSize:11, whiteSpace:'nowrap'}}>طلب صيانة</button>
                      <button className="btn btn-ghost btn-sm" style={{borderRadius:'var(--r-1)', fontSize:11, whiteSpace:'nowrap'}}>تفاصيل</button>
                    </div>
                  </div>
                ))}
              </div>

              <div style={{
                marginTop:20, padding:'16px 20px',
                border:'1px solid var(--rule)', borderRadius:'var(--r-2)',
                background:'var(--surface)', display:'flex', justifyContent:'space-between', alignItems:'center'
              }}>
                <div style={{fontSize:14, color:'var(--ink-3)'}}>
                  <span style={{fontWeight:600, color:'var(--indigo)'}}>سياسة الضمان:</span> كل المنتجات الفيزيائية تأتي بضمان 12 شهر من نيوجن ستور. البطاقات الرقمية مستثناة.
                </div>
                <a href="#" style={{fontFamily:'var(--f-wordmark)', fontSize:13, fontWeight:600, color:'var(--accent-deep)', whiteSpace:'nowrap', marginInlineStart:16}}>قرأ سياسة الضمان →</a>
              </div>
            </div>
          )}

          {/* Addresses tab — now index 4 */}
          {activeTab === 4 && (
            <div>
              <div style={{display:'flex', justifyContent:'space-between', alignItems:'flex-end', marginBottom:24}}>
                <div>
                  <div className="section-mark" style={{marginBottom:10}}><span>03</span><span style={{color:'var(--ink)'}}>· العناوين · ADDRESSES</span></div>
                  <h2 className="t-h2" style={{margin:0, color:'var(--indigo)'}}>عناوين الشحن</h2>
                </div>
                <button className="btn btn-sm" style={{borderRadius:'var(--r-2)'}}>+ إضافة عنوان</button>
              </div>
              <div style={{display:'grid', gridTemplateColumns:'1fr 1fr', gap:16}}>
                {addresses.map((a,i)=>(
                  <div key={i} style={{
                    background:'var(--surface)', border:`1px solid ${a.default?'var(--accent)':'var(--rule)'}`,
                    borderRadius:'var(--r-2)', padding:24, boxShadow:'var(--shadow-sm)', position:'relative'
                  }}>
                    {a.default && <span className="chip chip-sky" style={{position:'absolute', top:16, insetInlineEnd:16, fontSize:9}}>افتراضي</span>}
                    <div className="mono-up" style={{color:'var(--dim)', marginBottom:12}}>{a.label}</div>
                    <div style={{fontWeight:700, fontSize:15, color:'var(--indigo)', marginBottom:6}}>{a.name}</div>
                    <div style={{fontSize:14, color:'var(--ink-3)', lineHeight:1.6}}>
                      <div>{a.line1}</div>
                      <div>{a.line2}</div>
                      <div style={{marginTop:4}}>{a.country}</div>
                    </div>
                    <div style={{display:'flex', gap:8, marginTop:16}}>
                      <button className="btn btn-ghost btn-sm" style={{borderRadius:'var(--r-1)', fontSize:12}}>تعديل</button>
                      {!a.default && <button className="btn btn-ghost btn-sm" style={{borderRadius:'var(--r-1)', fontSize:12, color:'var(--sale)'}}>حذف</button>}
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Settings tab — index 6 */}
          {activeTab === 6 && (
            <div>
              <div className="section-mark" style={{marginBottom:10}}><span>05</span><span style={{color:'var(--ink)'}}>· الإعدادات · SETTINGS</span></div>
              <h2 className="t-h2" style={{margin:'0 0 24px', color:'var(--indigo)'}}>إعدادات الحساب</h2>
              <div style={{display:'flex', flexDirection:'column', gap:16}}>
                {[
                  {title:'المعلومات الشخصية', fields:[['الاسم الأول','أحمد'],['اسم العائلة','السبيعي'],['البريد الإلكتروني','ahmad@example.sa'],['رقم الجوال','+966 50 123 4567']]},
                  {title:'كلمة المرور', fields:[['كلمة المرور الحالية','••••••••'],['كلمة المرور الجديدة',''],['تأكيد كلمة المرور','']]},
                ].map((section,si)=>(
                  <div key={si} style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', padding:24, boxShadow:'var(--shadow-sm)'}}>
                    <div style={{fontWeight:700, fontSize:16, color:'var(--indigo)', marginBottom:20, paddingBottom:16, borderBottom:'1px solid var(--rule)'}}>{section.title}</div>
                    <div style={{display:'grid', gridTemplateColumns:'1fr 1fr', gap:16}}>
                      {section.fields.map(([l,v],fi)=>(
                        <label key={fi} style={{display:'flex', flexDirection:'column', gap:6}}>
                          <span className="mono-up" style={{color:'var(--dim)', fontSize:9}}>{l}</span>
                          <input defaultValue={v} type={l.includes('المرور')?'password':'text'} style={{
                            padding:'11px 14px', border:'1px solid var(--rule)',
                            borderRadius:'var(--r-1)', background:'var(--bg)',
                            fontFamily:'var(--f-ar)', fontSize:14, color:'var(--ink)'
                          }}/>
                        </label>
                      ))}
                    </div>
                    <button className="btn btn-sm" style={{marginTop:20, borderRadius:'var(--r-1)'}}>حفظ التغييرات</button>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Payment tab — index 5 */}
          {activeTab === 5 && (
            <div>
              <div className="section-mark" style={{marginBottom:10}}><span>04</span><span style={{color:'var(--ink)'}}>· الدفع · PAYMENT</span></div>
              <h2 className="t-h2" style={{margin:'0 0 24px', color:'var(--indigo)'}}>طرق الدفع المحفوظة</h2>
              <div style={{display:'flex', flexDirection:'column', gap:12}}>
                {/* Digital wallets */}
                <div style={{background:'var(--surface-2)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', padding:'16px 20px'}}>
                  <div className="mono-up" style={{color:'var(--dim)', fontSize:9, marginBottom:14}}>محافظ رقمية · DIGITAL WALLETS</div>
                  <div style={{display:'grid', gridTemplateColumns:'1fr 1fr', gap:12}}>
                    {[
                      {name:'Apple Pay', icon:'', color:'#000', connected:true},
                      {name:'Google Pay', icon:'G', color:'#4285F4', connected:false},
                      {name:'STC Pay', icon:'S', color:'#7B2D8B', connected:true},
                      {name:'Mada Pay', icon:'M', color:'#1A2B4B', connected:false},
                    ].map((w,i)=>(
                      <div key={i} style={{
                        display:'flex', justifyContent:'space-between', alignItems:'center',
                        padding:'14px 16px', background:'var(--surface)',
                        border:'1px solid var(--rule)', borderRadius:'var(--r-2)'
                      }}>
                        <div style={{display:'flex', gap:12, alignItems:'center'}}>
                          <div style={{
                            width:40, height:40, borderRadius:'var(--r-1)',
                            background:w.color, display:'grid', placeItems:'center',
                            fontFamily:'var(--f-wordmark)', fontWeight:700, fontSize:16, color:'#fff'
                          }}>{w.icon}</div>
                          <div>
                            <div style={{fontWeight:600, fontSize:14, color:'var(--indigo)'}}>{w.name}</div>
                            <div className="mono-up" style={{color: w.connected?'var(--good)':'var(--dim)', fontSize:9, marginTop:2}}>{w.connected?'● متصل':'○ غير متصل'}</div>
                          </div>
                        </div>
                        <button className={`btn btn-sm ${w.connected?'btn-ghost':''}`} style={{borderRadius:'var(--r-1)', fontSize:11}}>
                          {w.connected ? 'فصل' : 'ربط'}
                        </button>
                      </div>
                    ))}
                  </div>
                </div>

                {/* Saved cards */}
                <div className="mono-up" style={{color:'var(--dim)', fontSize:9, marginTop:4}}>بطاقات محفوظة · SAVED CARDS</div>
                {[
                  {type:'Mada', last4:'1234', exp:'12/27', default:true, color:'#1A2B4B'},
                  {type:'Visa', last4:'5678', exp:'08/26', default:false, color:'#1A1F71'},
                ].map((c,i)=>(
                  <div key={i} style={{
                    background:'var(--surface)', border:`1px solid ${c.default?'var(--accent)':'var(--rule)'}`,
                    borderRadius:'var(--r-2)', padding:'18px 24px',
                    display:'flex', justifyContent:'space-between', alignItems:'center',
                    boxShadow:'var(--shadow-sm)'
                  }}>
                    <div style={{display:'flex', gap:16, alignItems:'center'}}>
                      <div style={{
                        width:56, height:36, borderRadius:'var(--r-1)',
                        background:c.color, display:'grid', placeItems:'center',
                        fontFamily:'var(--f-wordmark)', fontWeight:700, fontSize:13, color:'#fff'
                      }}>{c.type}</div>
                      <div>
                        <div style={{fontWeight:600, color:'var(--indigo)'}}>•••• •••• •••• {c.last4}</div>
                        <div className="mono" style={{fontSize:11, color:'var(--dim)', marginTop:2}}>تنتهي {c.exp}</div>
                      </div>
                    </div>
                    <div style={{display:'flex', gap:8, alignItems:'center'}}>
                      {c.default && <span className="chip chip-sky" style={{fontSize:9}}>افتراضي</span>}
                      <button className="btn btn-ghost btn-sm" style={{borderRadius:'var(--r-1)', fontSize:12}}>إزالة</button>
                    </div>
                  </div>
                ))}
                <button className="btn btn-ghost btn-sm" style={{alignSelf:'flex-start', borderRadius:'var(--r-2)'}}>+ إضافة بطاقة جديدة</button>
              </div>
            </div>
          )}
        </main>
      </div>
      <Footer/>
    </div>
  );
};
window.Account = Account;
