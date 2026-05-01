// Checkout
const Checkout = () => (
  <div dir="rtl" style={{background:'var(--bg)', minHeight:'100%', fontFamily:'var(--f-ar)'}}>
    <TopBar/>
    {/* Slim header */}
    <header style={{borderBottom:'1px solid var(--rule)', padding:'20px 32px'}}>
      <div style={{maxWidth:1440, margin:'0 auto', display:'flex', justifyContent:'space-between', alignItems:'center'}}>
        <div style={{display:'flex', gap:12, alignItems:'center'}}>
          <div style={{width:32, height:32, background:'var(--ink)', color:'var(--paper)', display:'grid', placeItems:'center', fontFamily:'var(--f-mono)', fontWeight:700, fontSize:13}}>NG</div>
          <span className="mono-up">NEOGEN STORE · CHECKOUT</span>
        </div>
        <div style={{display:'flex', gap:0, alignItems:'center'}}>
          {[['01','السلة',false],['02','الشحن',true],['03','الدفع',false]].map(([n,l,a],i)=>(
            <React.Fragment key={i}>
              <div style={{display:'flex', alignItems:'center', gap:10, opacity: a?1:0.4}}>
                <span className="mono-up" style={{
                  width:28, height:28, border:'1px solid var(--ink)', display:'grid', placeItems:'center',
                  background: a?'var(--ink)':'transparent', color: a?'var(--paper)':'var(--ink)'
                }}>{n}</span>
                <span style={{fontSize:13, fontWeight:500}}>{l}</span>
              </div>
              {i<2 && <span style={{width:24, height:1, background:'var(--rule)', margin:'0 14px'}}></span>}
            </React.Fragment>
          ))}
        </div>
        <div className="mono-up" style={{color:'var(--ink-4)'}}>● اتصال آمن SSL</div>
      </div>
    </header>

    <div style={{maxWidth:1280, margin:'0 auto', padding:'48px 32px', display:'grid', gridTemplateColumns:'1.6fr 1fr', gap:48}}>
      {/* Forms */}
      <div style={{display:'flex', flexDirection:'column', gap:32}}>
        {/* Contact */}
        <section style={{border:'1px solid var(--rule)', padding:28}}>
          <div style={{display:'flex', justifyContent:'space-between', marginBottom:20}}>
            <div>
              <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:6}}>القسم 01</div>
              <h2 style={{margin:0, fontSize:20, fontWeight:600}}>التواصل</h2>
            </div>
            <span className="chip chip-accent">● مكتمل</span>
          </div>
          <div style={{display:'grid', gridTemplateColumns:'1fr 1fr', gap:16}}>
            <Input label="البريد الإلكتروني" value="ahmad@example.sa"/>
            <Input label="رقم الجوال" value="+966 50 123 4567"/>
          </div>
        </section>

        {/* Shipping */}
        <section style={{border:'1px solid var(--ink)', padding:28}}>
          <div style={{display:'flex', justifyContent:'space-between', marginBottom:20}}>
            <div>
              <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:6}}>القسم 02 · جارٍ الإكمال</div>
              <h2 style={{margin:0, fontSize:20, fontWeight:600}}>عنوان الشحن</h2>
            </div>
          </div>
          {/* Country */}
          <div style={{marginBottom:16}}>
            <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:8}}>الدولة · GCC</div>
            <div style={{display:'flex', gap:8, flexWrap:'wrap'}}>
              {[['SA','السعودية',true],['AE','الإمارات'],['KW','الكويت'],['BH','البحرين'],['OM','عُمان'],['QA','قطر']].map(([c,n,a],i)=>(
                <button key={i} className={a?"chip chip-solid":"chip"} style={{padding:'10px 14px', fontSize:11, cursor:'pointer'}}>
                  <span className="en" style={{marginInlineEnd:6}}>{c}</span>{n}
                </button>
              ))}
            </div>
          </div>
          <div style={{display:'grid', gridTemplateColumns:'1fr 1fr', gap:16}}>
            <Input label="الاسم الأول" value="أحمد"/>
            <Input label="اسم العائلة" value="السبيعي"/>
            <Input label="المدينة" value="الرياض"/>
            <Input label="الحي" value="الياسمين"/>
            <Input label="العنوان" value="شارع الأمير محمد بن عبدالعزيز" full/>
            <Input label="الرمز البريدي" value="13325"/>
            <Input label="رقم المبنى" value="3421"/>
          </div>
          {/* Shipping method */}
          <div style={{marginTop:24, paddingTop:24, borderTop:'1px dashed var(--rule)'}}>
            <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:12}}>اختر شركة الشحن · DELIVERY CARRIER</div>
            <div style={{display:'flex', flexDirection:'column', gap:8}}>
              {[
                {logo:'📦', name:'Aramex Standard', price:'25 SAR', eta:'2–5 أيام عمل', rating:'4.7★', active:true,  badge:null,       features:['تتبع مباشر','SMS تنبيهات']},
                {logo:'⚡', name:'SMSA Express',    price:'35 SAR', eta:'1–2 يوم عمل',  rating:'4.8★', active:false, badge:null,       features:['تسليم سريع','تتبع مباشر','واتساب']},
                {logo:'🌍', name:'DHL Express GCC', price:'75 SAR', eta:'1–3 أيام خليج',rating:'4.9★', active:false, badge:null,       features:['شحن GCC','تتبع دقيق','مضمون']},
                {logo:'🏠', name:'نفس اليوم',       price:'55 SAR', eta:'اليوم قبل 9م', rating:'4.6★', active:false, badge:'الرياض فقط', features:['الرياض فقط','تحديد الوقت']},
              ].map((c,i)=>(
                <label key={i} style={{
                  display:'flex', gap:14, padding:'14px 16px',
                  border:`1px solid ${c.active?'var(--accent)':'var(--rule)'}`,
                  background: c.active?'var(--accent-wash)':'var(--surface)',
                  borderRadius:'var(--r-2)', cursor:'pointer', alignItems:'flex-start'
                }}>
                  <div style={{marginTop:3, width:16, height:16, borderRadius:'50%', border:'2px solid var(--ink-3)', background:c.active?'var(--indigo)':'transparent', boxShadow:c.active?'inset 0 0 0 3px var(--surface)':'none', flexShrink:0}}></div>
                  <span style={{fontSize:20, flexShrink:0}}>{c.logo}</span>
                  <div style={{flex:1}}>
                    <div style={{display:'flex', justifyContent:'space-between', alignItems:'flex-start'}}>
                      <div style={{fontWeight:700, fontSize:14, color:'var(--indigo)', display:'flex', gap:8, alignItems:'center'}}>
                        {c.name}
                        {c.badge && <span className="chip chip-sale" style={{fontSize:8}}>{c.badge}</span>}
                      </div>
                      <div style={{textAlign:'end'}}>
                        <div style={{fontFamily:'var(--f-wordmark)', fontWeight:700, fontSize:15, color:'var(--indigo)'}}>{c.price}</div>
                        <div className="mono" style={{fontSize:10, color:'var(--accent-deep)', marginTop:1}}>{c.eta}</div>
                      </div>
                    </div>
                    <div style={{fontFamily:'var(--f-wordmark)', fontSize:10, color:'var(--dim)', marginTop:2}}>{c.rating}</div>
                    <div style={{display:'flex', gap:5, marginTop:6, flexWrap:'wrap'}}>
                      {c.features.map((f,fi)=>(
                        <span key={fi} style={{fontFamily:'var(--f-mono)', fontSize:9, padding:'2px 6px', border:'1px solid var(--rule)', borderRadius:2, color:'var(--ink-4)', background:'var(--surface)'}}>✓ {f}</span>
                      ))}
                    </div>
                  </div>
                </label>
              ))}
            </div>
          </div>
        </section>

        {/* Payment */}
        <section style={{border:'1px solid var(--rule)', padding:28, opacity:0.5}}>
          <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:6}}>القسم 03 · بانتظار الإكمال</div>
          <h2 style={{margin:0, fontSize:20, fontWeight:600}}>الدفع</h2>
        </section>
      </div>

      {/* Summary */}
      <aside>
        <div style={{border:'1px solid var(--ink)', padding:24, position:'sticky', top:24}}>
          <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:16}}>طلبك · 3 منتجات</div>
          <div style={{display:'flex', flexDirection:'column', gap:14, paddingBottom:16, borderBottom:'1px dashed var(--rule)'}}>
            {[
              ['UDM-Pro','NG-ENT-003',1,2054],
              ['Omada EAP650','NT-WAP-TPL-002',2,799],
              ['Cat6a 305m','NT-CBL-GEN-001',1,740]
            ].map(([n,sku,q,p],i)=>(
              <div key={i} style={{display:'flex', gap:12}}>
                <div className="ph" style={{width:48, height:48, flexShrink:0}}><span className="ph-label" style={{fontSize:8}}>img</span></div>
                <div style={{flex:1}}>
                  <div style={{fontSize:13, fontWeight:500}}>{n}</div>
                  <div className="mono" style={{fontSize:10, color:'var(--ink-4)', marginTop:2}}>{sku} · {q}×</div>
                </div>
                <div className="en" style={{fontSize:13, fontWeight:500}}>{(p*q).toLocaleString('en-US')}</div>
              </div>
            ))}
          </div>
          <div style={{display:'flex', flexDirection:'column', gap:8, padding:'16px 0', borderBottom:'1px dashed var(--rule)'}}>
            {[['المجموع الفرعي','4,392'],['الشحن (Aramex)','25'],['ضريبة 15%','663']].map(([l,v],i)=>(
              <div key={i} style={{display:'flex', justifyContent:'space-between', fontSize:13}}>
                <span style={{color:'var(--ink-3)'}}>{l}</span>
                <span className="en" style={{fontWeight:500}}>{v} SAR</span>
              </div>
            ))}
          </div>
          <div style={{display:'flex', justifyContent:'space-between', padding:'16px 0', borderBottom:'1px solid var(--ink)'}}>
            <span style={{fontWeight:600}}>الإجمالي</span>
            <div className="price"><span className="price-now" style={{fontSize:22}}>5,080</span><span className="price-sar">SAR</span></div>
          </div>
          <button className="btn" style={{width:'100%', justifyContent:'center', marginTop:16, padding:'16px'}}>إكمال الطلب · ادفع 5,080 SAR</button>
          <div className="mono-up" style={{color:'var(--ink-4)', textAlign:'center', marginTop:12, fontSize:9}}>● معالجة آمنة عبر MADA · STRIPE</div>
        </div>
      </aside>
    </div>
  </div>
);

const Input = ({label, value, full}) => (
  <label style={{display:'flex', flexDirection:'column', gap:6, gridColumn: full?'1 / -1':'auto'}}>
    <span className="mono-up" style={{color:'var(--ink-4)'}}>{label}</span>
    <input defaultValue={value} style={{padding:'12px 14px', border:'1px solid var(--rule)', background:'var(--paper)', fontFamily:'var(--f-ar)', fontSize:14, color:'var(--ink)'}}/>
  </label>
);

window.Checkout = Checkout;
