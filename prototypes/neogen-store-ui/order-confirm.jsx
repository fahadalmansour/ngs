// Order Confirmation Page
const OrderConfirmation = () => {
  const items = [
    {sku:'NG-ENT-003', ar:'Ubiquiti UniFi Dream Machine Pro', qty:1, price:2054, ph:'UDM-Pro'},
    {sku:'NT-WAP-TPL-002', ar:'TP-Link Omada EAP650', qty:2, price:799, ph:'EAP650'},
    {sku:'NT-CBL-GEN-001', ar:'كابل Cat6a مصفّح 305م', qty:1, price:740, ph:'cat6a'},
  ];
  const total = 5080;
  const orderNum = 'NG-2026-04721';
  return (
    <div dir="rtl" style={{background:'var(--bg)', minHeight:'100%', fontFamily:'var(--f-ar)'}}>
      <TopBar/>
      <Header/>
      <div style={{maxWidth:960, margin:'0 auto', padding:'64px 32px 96px'}}>
        {/* Success banner */}
        <div style={{
          background:`linear-gradient(135deg, var(--indigo-deep), var(--indigo))`,
          borderRadius:'var(--r-3)', padding:'48px 40px', marginBottom:40,
          color:'#fff', position:'relative', overflow:'hidden'
        }}>
          <div style={{
            position:'absolute', inset:0, opacity:0.06,
            background:'linear-gradient(rgba(255,255,255,0.15) 1px,transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.15) 1px,transparent 1px)',
            backgroundSize:'48px 48px'
          }}></div>
          <div style={{position:'relative', display:'flex', flexDirection:'column', gap:16, alignItems:'flex-start'}}>
            <div style={{
              width:56, height:56, borderRadius:'50%',
              background:'var(--accent)', display:'grid', placeItems:'center',
              fontSize:24, boxShadow:'0 0 0 8px rgba(56,189,248,0.2)'
            }}>✓</div>
            <div>
              <div className="mono-up" style={{color:'rgba(255,255,255,0.5)', marginBottom:8}}>تم الطلب · ORDER PLACED</div>
              <h1 style={{margin:0, fontSize:36, fontWeight:700, lineHeight:1.1}}>شكراً! طلبك في الطريق.</h1>
              <p style={{margin:'12px 0 0', fontSize:16, color:'rgba(255,255,255,0.65)', lineHeight:1.6}}>
                سنرسل لك رمز التتبع على بريدك الإلكتروني خلال ساعة. متوقع الوصول خلال 2–5 أيام عمل.
              </p>
            </div>
            <div style={{display:'flex', gap:24, marginTop:8, flexWrap:'wrap'}}>
              {[['رقم الطلب', orderNum],['المبلغ', `${total.toLocaleString('en-US')} SAR`],['الشحن','Aramex · 2–5 أيام'],['الضمان','12 شهر']].map(([k,v],i)=>(
                <div key={i}>
                  <div style={{fontFamily:'var(--f-mono)', fontSize:10, textTransform:'uppercase', letterSpacing:'0.08em', color:'rgba(255,255,255,0.4)', marginBottom:4}}>{k}</div>
                  <div style={{fontFamily:'var(--f-wordmark)', fontWeight:600, fontSize:15, color:'var(--accent)'}}>{v}</div>
                </div>
              ))}
            </div>
          </div>
        </div>

        <div style={{display:'grid', gridTemplateColumns:'1.4fr 1fr', gap:24}}>
          {/* Order items */}
          <div>
            <div style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', overflow:'hidden'}}>
              <div style={{padding:'16px 20px', borderBottom:'1px solid var(--rule)', display:'flex', justifyContent:'space-between', alignItems:'center', background:'var(--surface-2)'}}>
                <span className="mono-up" style={{color:'var(--ink-4)'}}>محتويات الطلب</span>
                <span className="mono-up" style={{color:'var(--dim)', fontSize:9}}>{items.length} منتجات</span>
              </div>
              {items.map((it,i)=>(
                <div key={i} style={{display:'grid', gridTemplateColumns:'64px 1fr auto', gap:16, padding:'16px 20px', borderBottom: i<items.length-1?'1px solid var(--rule)':'none', alignItems:'center'}}>
                  <div className="ph" style={{width:64, height:64}}><span className="ph-label" style={{fontSize:8}}>{it.ph}</span></div>
                  <div>
                    <div className="mono" style={{fontSize:10, color:'var(--dim)', marginBottom:3}}>{it.sku}</div>
                    <div style={{fontWeight:600, fontSize:14}}>{it.ar}</div>
                    <div className="mono-up" style={{color:'var(--dim)', fontSize:9, marginTop:3}}>الكمية · {it.qty}</div>
                  </div>
                  <div className="price"><span className="price-now" style={{fontSize:15}}>{(it.price*it.qty).toLocaleString('en-US')}</span><span className="price-sar">SAR</span></div>
                </div>
              ))}
            </div>

            {/* Tracking timeline */}
            <div style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', padding:'20px', marginTop:16}}>
              <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:20}}>تتبع الطلب · TRACKING</div>
              <div style={{display:'flex', flexDirection:'column', gap:0}}>
                {[
                  {s:'تم استلام الطلب', t:'1 مايو 2026 · 3:42م', done:true, active:false},
                  {s:'جارٍ التجهيز', t:'2 مايو 2026 · متوقع', done:false, active:true},
                  {s:'تم الشحن مع Aramex', t:'3 مايو 2026 · متوقع', done:false, active:false},
                  {s:'تم التسليم', t:'5–7 مايو 2026 · متوقع', done:false, active:false},
                ].map((step,i,arr)=>(
                  <div key={i} style={{display:'grid', gridTemplateColumns:'32px 1fr', gap:12}}>
                    <div style={{display:'flex', flexDirection:'column', alignItems:'center'}}>
                      <div style={{
                        width:28, height:28, borderRadius:'50%', flexShrink:0,
                        background: step.done?'var(--good)':step.active?'var(--accent)':'var(--surface-2)',
                        border:`2px solid ${step.done?'var(--good)':step.active?'var(--accent)':'var(--rule)'}`,
                        display:'grid', placeItems:'center', fontSize:13, color:'#fff', fontWeight:700
                      }}>{step.done?'✓':step.active?'◉':''}</div>
                      {i<arr.length-1 && <div style={{width:2, flex:1, background: step.done?'var(--good)':'var(--rule)', margin:'4px 0'}}></div>}
                    </div>
                    <div style={{paddingBottom: i<arr.length-1?20:0}}>
                      <div style={{fontWeight:600, fontSize:14, color: step.active?'var(--accent-ink)':step.done?'var(--ink)':'var(--dim)'}}>{step.s}</div>
                      <div className="mono" style={{fontSize:11, color:'var(--dim)', marginTop:2}}>{step.t}</div>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </div>

          {/* Summary sidebar */}
          <div style={{display:'flex', flexDirection:'column', gap:16}}>
            {/* Total summary */}
            <div style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', padding:20}}>
              <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:16}}>ملخّص الدفع</div>
              {[['المجموع الفرعي','4,392 SAR'],['الشحن (Aramex)','25 SAR'],['ضريبة 15%','663 SAR']].map(([l,v],i)=>(
                <div key={i} style={{display:'flex', justifyContent:'space-between', padding:'8px 0', borderBottom:'1px dashed var(--rule)', fontSize:13}}>
                  <span style={{color:'var(--ink-3)'}}>{l}</span>
                  <span className="en" style={{fontWeight:500}}>{v}</span>
                </div>
              ))}
              <div style={{display:'flex', justifyContent:'space-between', padding:'16px 0 0', fontWeight:700}}>
                <span>الإجمالي</span>
                <div className="price"><span className="price-now" style={{fontSize:20}}>5,080</span><span className="price-sar">SAR</span></div>
              </div>
            </div>
            {/* Shipping address */}
            <div style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', padding:20}}>
              <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:12}}>عنوان الشحن</div>
              <div style={{fontSize:14, color:'var(--ink-2)', lineHeight:1.7}}>
                <div style={{fontWeight:600}}>أحمد السبيعي</div>
                <div>شارع الأمير محمد بن عبدالعزيز</div>
                <div>حي الياسمين، الرياض 13325</div>
                <div>🇸🇦 المملكة العربية السعودية</div>
              </div>
            </div>
            {/* Actions */}
            <button className="btn btn-dark" style={{width:'100%', justifyContent:'center', borderRadius:'var(--r-2)'}}>تتبع الطلب</button>
            <button className="btn btn-ghost" style={{width:'100%', justifyContent:'center', borderRadius:'var(--r-2)'}}>متابعة التسوق</button>
            {/* Recommendations */}
            <div style={{background:'var(--accent-wash)', border:'1px solid var(--accent-soft)', borderRadius:'var(--r-2)', padding:16}}>
              <div className="mono-up" style={{color:'var(--accent-deep)', marginBottom:8}}>قد يعجبك أيضاً</div>
              <div style={{fontSize:13, color:'var(--ink-3)'}}>بناءً على طلبك — لديك كابل SFP+ DAC بـ 80 SAR يكمل إعدادك.</div>
              <button className="btn btn-sm" style={{marginTop:12, borderRadius:'var(--r-1)'}}>اضف للطلب القادم</button>
            </div>
          </div>
        </div>
      </div>
      <Footer/>
    </div>
  );
};
window.OrderConfirmation = OrderConfirmation;
