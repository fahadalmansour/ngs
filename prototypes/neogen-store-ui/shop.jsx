// Shop / Category listing
const Shop = () => {
  const filters = [
    {label:'الفئة', items:['الكل','بطاقات رقمية','البيت الذكي','الألعاب','هوم لاب','الشبكات','الأجهزة','إكسسوارات']},
    {label:'العلامة', items:['Ubiquiti','TP-Link','Aqara','MinisForum','DJI','Apple','Elgato','HyperX']},
    {label:'السعر', items:['أقل من 200','200–500','500–1500','1500–5000','+5000']},
    {label:'الحالة', items:['متوفر','تخفيض','جديد']},
  ];
  return (
    <div dir="rtl" style={{background:'var(--bg)', minHeight:'100%', fontFamily:'var(--f-ar)'}}>
      <TopBar/>
      <Header/>
      {/* Crumbs + heading */}
      <section style={{borderBottom:'1px solid var(--rule)'}}>
        <div style={{maxWidth:1440, margin:'0 auto', padding:'48px 32px 32px'}}>
          <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:24}}>الرئيسية / المتجر</div>
          <div style={{display:'flex', justifyContent:'space-between', alignItems:'flex-end', flexWrap:'wrap', gap:24}}>
            <div>
              <h1 className="t-h1" style={{margin:0}}>المتجر <span className="en" style={{color:'var(--ink-4)', fontWeight:400, fontSize:'0.5em'}}>· catalog</span></h1>
              <p className="t-body" style={{margin:'12px 0 0', maxWidth:560}}>215 منتج عبر 6 فئات. كل وحدة اختبرناها قبل الإضافة.</p>
            </div>
            <div style={{display:'flex', gap:8}}>
              <select style={{padding:'10px 14px', border:'1px solid var(--rule)', background:'var(--paper)', fontFamily:'var(--f-ar)', fontSize:13}}>
                <option>ترتيب: الأكثر صلة</option>
                <option>السعر: من الأقل</option>
                <option>السعر: من الأعلى</option>
                <option>الأحدث</option>
              </select>
              <div style={{display:'flex', border:'1px solid var(--rule)'}}>
                <button className="btn-sm" style={{background:'var(--ink)', color:'var(--paper)', border:'none', padding:'10px 12px', fontSize:12}}>⚏ شبكة</button>
                <button className="btn-sm" style={{background:'transparent', color:'var(--ink)', border:'none', borderInlineStart:'1px solid var(--rule)', padding:'10px 12px', fontSize:12}}>≡ قائمة</button>
              </div>
            </div>
          </div>
        </div>
      </section>

      <div style={{maxWidth:1440, margin:'0 auto', padding:'32px 32px 96px', display:'grid', gridTemplateColumns:'260px 1fr', gap:32}}>
        {/* Sidebar filters */}
        <aside>
          <div style={{position:'sticky', top:24}}>
            <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:12, paddingBottom:12, borderBottom:'1px solid var(--ink)'}}>الفلاتر · FILTERS</div>
            {filters.map((f, i) => (
              <div key={i} style={{borderBottom:'1px solid var(--rule)', padding:'18px 0'}}>
                <div style={{display:'flex', justifyContent:'space-between', marginBottom:12}}>
                  <span style={{fontWeight:600, fontSize:13}}>{f.label}</span>
                  <span className="mono" style={{color:'var(--ink-4)', fontSize:11}}>−</span>
                </div>
                <div style={{display:'flex', flexDirection:'column', gap:8}}>
                  {f.items.map((it, j) => (
                    <label key={j} style={{display:'flex', alignItems:'center', gap:8, fontSize:13, color:'var(--ink-2)', cursor:'pointer'}}>
                      <span style={{width:14, height:14, border:'1px solid var(--ink-3)', display:'inline-block', flexShrink:0, background: (i===0&&j===0) ? 'var(--ink)':'transparent'}}></span>
                      <span style={{flex:1}}>{it}</span>
                      <span className="mono" style={{color:'var(--ink-4)', fontSize:11}}>{Math.floor(20+Math.random()*80)}</span>
                    </label>
                  ))}
                </div>
              </div>
            ))}
            <button className="btn btn-ghost btn-sm" style={{border:'1px solid var(--ink)', width:'100%', justifyContent:'center', marginTop:16}}>مسح الكل</button>
          </div>
        </aside>

        {/* Grid */}
        <div>
          {/* Active filters */}
          <div style={{display:'flex', gap:8, marginBottom:24, alignItems:'center', flexWrap:'wrap'}}>
            <span className="mono-up" style={{color:'var(--ink-4)'}}>نشطة:</span>
            {['الشبكات','Ubiquiti','500–1500'].map((t,i)=>(
              <span key={i} className="chip chip-solid" style={{padding:'6px 10px', fontSize:11}}>{t} ✕</span>
            ))}
            <span className="mono" style={{color:'var(--ink-4)', fontSize:12, marginInlineStart:'auto'}}>عرض 1–12 من 215</span>
          </div>
          <div style={{display:'grid', gridTemplateColumns:'repeat(3,1fr)', gap:16}}>
            {[...SAMPLE_PRODUCTS, ...SAMPLE_PRODUCTS.slice(0,4)].map((p,i)=><ProductCard key={i} {...p}/>)}
          </div>
          {/* Pagination */}
          <div style={{display:'flex', justifyContent:'space-between', alignItems:'center', marginTop:48, paddingTop:24, borderTop:'1px solid var(--rule)'}}>
            <span className="mono-up" style={{color:'var(--ink-4)'}}>الصفحة 01 من 18</span>
            <div style={{display:'flex', gap:4}}>
              {['‹','01','02','03','...','18','›'].map((p,i)=>(
                <button key={i} className={i===1?"btn btn-sm":"btn btn-ghost btn-sm"} style={{padding:'8px 14px', fontSize:12, border:'1px solid '+(i===1?'var(--ink)':'var(--rule)')}}>{p}</button>
              ))}
            </div>
          </div>
        </div>
      </div>
      <Footer/>
    </div>
  );
};
window.Shop = Shop;
