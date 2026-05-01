// Cart
const Cart = () => {
  const items = [
    {sku:'NG-ENT-003', ar:'Ubiquiti UniFi Dream Machine Pro', en:'UDM-Pro · 1U Rack', price:2054, was:2570, qty:1, ph:'UDM-Pro'},
    {sku:'NT-WAP-TPL-002', ar:'TP-Link Omada EAP650 نقطة وصول', en:'EAP650 · WiFi 6 AX3000', price:799, was:919, qty:2, ph:'EAP650'},
    {sku:'NT-CBL-GEN-001', ar:'كابل Cat6a مصفّح (305م)', en:'Cat6a Bulk · 305m', price:740, qty:1, ph:'cat6a coil'},
  ];
  const sub = items.reduce((s,i)=>s+i.price*i.qty,0);
  const ship = 25, vat = Math.round(sub*0.15), total = sub+ship+vat;
  return (
    <div dir="rtl" style={{background:'var(--bg)', minHeight:'100%', fontFamily:'var(--f-ar)'}}>
      <TopBar/>
      <Header/>
      <div style={{maxWidth:1440, margin:'0 auto', padding:'48px 32px 96px'}}>
        <div style={{display:'flex', justifyContent:'space-between', alignItems:'flex-end', marginBottom:32}}>
          <div>
            <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:12}}>الرئيسية / السلة</div>
            <h1 className="t-h1" style={{margin:0}}>السلة <span className="en" style={{color:'var(--ink-4)', fontWeight:400}}>· cart</span></h1>
          </div>
          {/* Stepper */}
          <div style={{display:'flex', gap:0, alignItems:'center'}}>
            {[['01','السلة',true],['02','الشحن',false],['03','الدفع',false]].map(([n,l,a],i)=>(
              <React.Fragment key={i}>
                <div style={{display:'flex', alignItems:'center', gap:10, opacity: a?1:0.4}}>
                  <span className="mono-up" style={{
                    width:28, height:28, border:'1px solid var(--ink)', display:'grid', placeItems:'center',
                    background: a?'var(--ink)':'transparent', color: a?'var(--paper)':'var(--ink)'
                  }}>{n}</span>
                  <span style={{fontSize:13, fontWeight:500}}>{l}</span>
                </div>
                {i<2 && <span style={{width:32, height:1, background:'var(--rule)', margin:'0 14px'}}></span>}
              </React.Fragment>
            ))}
          </div>
        </div>

        <div style={{display:'grid', gridTemplateColumns:'1.6fr 1fr', gap:48}}>
          {/* Items */}
          <div style={{border:'1px solid var(--rule)'}}>
            <div style={{display:'grid', gridTemplateColumns:'2fr 1fr 1fr 1fr 40px', padding:'14px 20px', borderBottom:'1px solid var(--ink)', background:'var(--paper-2)'}}>
              {['المنتج','السعر','الكمية','الإجمالي',''].map((h,i)=>(
                <span key={i} className="mono-up" style={{color:'var(--ink-4)'}}>{h}</span>
              ))}
            </div>
            {items.map((it,i)=>(
              <div key={i} style={{display:'grid', gridTemplateColumns:'2fr 1fr 1fr 1fr 40px', padding:20, borderBottom: i<2?'1px solid var(--rule)':'none', alignItems:'center', gap:16}}>
                <div style={{display:'flex', gap:16, alignItems:'center'}}>
                  <div className="ph" style={{width:80, height:80, flexShrink:0}}>
                    <span className="ph-label" style={{fontSize:9}}>{it.ph}</span>
                  </div>
                  <div>
                    <div className="mono" style={{fontSize:11, color:'var(--ink-4)'}}>{it.sku}</div>
                    <div style={{fontWeight:600, fontSize:14, marginTop:2}}>{it.ar}</div>
                    <div className="en" style={{fontSize:12, color:'var(--ink-4)', marginTop:2}}>{it.en}</div>
                  </div>
                </div>
                <div className="price"><span className="en" style={{fontSize:14, fontWeight:600}}>{it.price.toLocaleString('en-US')}</span><span className="price-sar">SAR</span></div>
                <div style={{display:'flex', border:'1px solid var(--rule)', width:'fit-content'}}>
                  <button style={{padding:'6px 10px', background:'transparent', border:'none'}}>−</button>
                  <span className="en" style={{padding:'6px 14px', fontWeight:600, borderInline:'1px solid var(--rule)'}}>{it.qty}</span>
                  <button style={{padding:'6px 10px', background:'transparent', border:'none'}}>+</button>
                </div>
                <div className="price"><span className="en" style={{fontSize:15, fontWeight:600}}>{(it.price*it.qty).toLocaleString('en-US')}</span><span className="price-sar">SAR</span></div>
                <button style={{background:'transparent', border:'none', color:'var(--ink-4)', fontSize:16, cursor:'pointer'}}>✕</button>
              </div>
            ))}
            <div style={{padding:20, borderTop:'1px solid var(--ink)', display:'flex', justifyContent:'space-between'}}>
              <a href="#" style={{fontSize:13, color:'var(--ink)', borderBottom:'1px solid var(--ink)', paddingBottom:2}}>← متابعة التسوق</a>
              <a href="#" style={{fontSize:13, color:'var(--ink-4)'}}>إفراغ السلة</a>
            </div>
          </div>

          {/* Summary */}
          <aside>
            <div style={{border:'1px solid var(--ink)', padding:24}}>
              <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:16}}>ملخّص الطلب · ORDER</div>
              <div style={{display:'flex', flexDirection:'column', gap:10, paddingBottom:16, borderBottom:'1px dashed var(--rule)'}}>
                {[['المجموع الفرعي', sub],['الشحن (المملكة)', ship],['ضريبة القيمة المضافة 15%', vat]].map(([l,v],i)=>(
                  <div key={i} style={{display:'flex', justifyContent:'space-between', fontSize:13}}>
                    <span style={{color:'var(--ink-3)'}}>{l}</span>
                    <span className="en" style={{fontWeight:500}}>{v.toLocaleString('en-US')} SAR</span>
                  </div>
                ))}
              </div>
              <div style={{display:'flex', justifyContent:'space-between', alignItems:'baseline', padding:'16px 0', borderBottom:'1px solid var(--ink)'}}>
                <span style={{fontWeight:600, fontSize:14}}>الإجمالي</span>
                <div className="price"><span className="price-now" style={{fontSize:24}}>{total.toLocaleString('en-US')}</span><span className="price-sar">SAR</span></div>
              </div>
              {/* Coupon */}
              <div style={{display:'flex', gap:0, marginTop:16}}>
                <input placeholder="كود الخصم" style={{flex:1, padding:'12px 14px', border:'1px solid var(--rule)', borderInlineEnd:'none', background:'var(--paper)', fontFamily:'var(--f-ar)', fontSize:13}}/>
                <button className="btn btn-sm" style={{borderRadius:0, padding:'12px 18px'}}>تطبيق</button>
              </div>
              <button className="btn" style={{width:'100%', justifyContent:'center', marginTop:16, padding:'16px'}}>متابعة للدفع →</button>
              <div style={{display:'flex', justifyContent:'center', gap:10, marginTop:16, opacity:0.7}}>
                {['mada','visa','mc','apple-pay','stc-pay'].map((p,i)=>(
                  <span key={i} className="mono-up" style={{fontSize:9, padding:'4px 6px', border:'1px solid var(--rule)'}}>{p}</span>
                ))}
              </div>
            </div>
            {/* Promise */}
            <div style={{marginTop:16, padding:20, border:'1px solid var(--rule)'}}>
              {[['● شحن من المملكة 2–5 أيام','SHIP'],['● ضمان 12 شهر','WARRANTY'],['● إرجاع 14 يوم','RETURN']].map(([l,k],i)=>(
                <div key={i} style={{display:'flex', justifyContent:'space-between', padding:'8px 0', borderBottom: i<2?'1px dashed var(--rule)':'none'}}>
                  <span style={{fontSize:13, color:'var(--ink-2)'}}>{l}</span>
                  <span className="mono-up" style={{color:'var(--ink-4)'}}>{k}</span>
                </div>
              ))}
            </div>
          </aside>
        </div>
      </div>
      <Footer/>
    </div>
  );
};
window.Cart = Cart;
