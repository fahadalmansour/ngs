// Product Category — NeoGen Store
const Category = ({catName='البيت الذكي', catEn='Smart Home', catIcon='🏠', count=48}) => {
  const subcats = {
    'البيت الذكي': ['المراكز الذكية','الإضاءة','الأمان','الطاقة','الأجهزة الذكية','الأتمتة'],
    'الألعاب': ['بطاقات الرسوميات','شاشات الألعاب','ملحقات','تسجيل البث','أجهزة التحكم','الأجهزة المحمولة'],
    'هوم لاب': ['Servers','Storage','Virtualization','Networking','Cooling','Accessories'],
    'الشبكات': ['نقاط وصول','موجهات','سويتش','كابلات','الحماية','VPN'],
    'الأجهزة': ['معالجات','ذاكرة','تخزين','طاقة','تبريد','هيكل'],
  }[catName] || ['الكل','الفئة الفرعية 1','الفئة الفرعية 2'];

  const [activeSub, setActiveSub] = React.useState('الكل');
  const [sortBy, setSortBy] = React.useState('الأكثر صلة');

  const products = SAMPLE_PRODUCTS.slice(0, 6);

  return (
    <div dir="rtl" style={{background:'var(--bg)', minHeight:'100%', fontFamily:'var(--f-ar)'}}>
      <TopBar/>
      <Header/>

      {/* Category hero bar */}
      <section style={{borderBottom:'1px solid var(--rule)', background:'var(--indigo)'}}>
        <div style={{maxWidth:1440, margin:'0 auto', padding:'40px 48px'}}>
          <div style={{display:'flex', justifyContent:'space-between', alignItems:'flex-end', gap:32, flexWrap:'wrap'}}>
            <div>
              <div style={{display:'flex', gap:8, alignItems:'center', marginBottom:16}}>
                <span style={{
                  fontFamily:'var(--f-mono)', fontSize:11, textTransform:'uppercase',
                  letterSpacing:'0.08em', color:'rgba(255,255,255,0.4)'
                }}>الرئيسية / المتجر</span>
                <span style={{color:'rgba(255,255,255,0.25)'}}>/</span>
                <span style={{fontFamily:'var(--f-mono)', fontSize:11, textTransform:'uppercase', letterSpacing:'0.08em', color:'rgba(255,255,255,0.6)'}}>{catName}</span>
              </div>
              <h1 style={{
                margin:0, fontFamily:'var(--f-wordmark)', fontSize:'clamp(32px,4vw,52px)',
                fontWeight:700, color:'#fff', lineHeight:1
              }}>
                {catName}
                <span style={{fontFamily:'var(--f-wordmark)', fontSize:'0.45em', fontWeight:400, color:'rgba(255,255,255,0.4)', marginRight:12}}>· {catEn}</span>
              </h1>
              <p style={{margin:'12px 0 0', fontSize:15, color:'rgba(255,255,255,0.55)'}}>{count} منتج مختار</p>
            </div>
            <div style={{display:'flex', gap:8, flexWrap:'wrap'}}>
              {[catIcon, catEn, count + ' منتج'].map((v,i)=>(
                <span key={i} className="chip chip-solid" style={{fontSize:12}}>{v}</span>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* Subcategory tabs */}
      <div style={{borderBottom:'1px solid var(--rule)', background:'var(--surface)', position:'sticky', top:0, zIndex:50}}>
        <div style={{maxWidth:1440, margin:'0 auto', padding:'0 48px', display:'flex', gap:0, overflowX:'auto'}} className="no-scrollbar">
          {['الكل', ...subcats].map((sub,i)=>(
            <button key={i} onClick={()=>setActiveSub(sub)} style={{
              padding:'14px 20px', border:'none', borderBottom:`2px solid ${activeSub===sub?'var(--accent)':'transparent'}`,
              background:'transparent', cursor:'pointer', fontFamily:'var(--f-ar)', fontSize:13,
              fontWeight: activeSub===sub?700:400,
              color: activeSub===sub?'var(--indigo)':'var(--ink-4)',
              whiteSpace:'nowrap', transition:'all .15s', flexShrink:0
            }}>{sub}</button>
          ))}
        </div>
      </div>

      <div style={{maxWidth:1440, margin:'0 auto', padding:'32px 48px 96px', display:'grid', gridTemplateColumns:'260px 1fr', gap:40, alignItems:'start'}}>

        {/* Filters */}
        <aside style={{position:'sticky', top:50}}>
          <div style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', overflow:'hidden'}}>
            <div style={{padding:'14px 20px', borderBottom:'1px solid var(--rule)', background:'var(--surface-2)', display:'flex', justifyContent:'space-between'}}>
              <span style={{fontFamily:'var(--f-mono)', fontSize:11, textTransform:'uppercase', letterSpacing:'0.08em', color:'var(--ink-4)'}}>الفلاتر</span>
              <button style={{fontFamily:'var(--f-mono)', fontSize:11, color:'var(--accent-deep)', cursor:'pointer'}}>مسح الكل</button>
            </div>
            {[
              {label:'السعر', items:['أقل من 200 ريال','200–500 ريال','500–1500 ريال','1500–5000 ريال','أكثر من 5000 ريال']},
              {label:'العلامة التجارية', items:['Aqara','Ubiquiti','TP-Link','Sonos','Apple','Samsung','Lutron']},
              {label:'الحالة', items:['متوفر','آخر القطع','عرض خاص','جديد']},
              {label:'التقييم', items:['5 نجوم','4 نجوم وأعلى','3 نجوم وأعلى']},
            ].map(({label,items},gi)=>(
              <div key={gi} style={{borderBottom:'1px solid var(--rule)'}}>
                <div style={{padding:'12px 20px', display:'flex', justifyContent:'space-between', alignItems:'center'}}>
                  <span style={{fontWeight:600, fontSize:13, color:'var(--ink)'}}>{label}</span>
                  <span style={{fontFamily:'var(--f-mono)', fontSize:14, color:'var(--ink-4)'}}>−</span>
                </div>
                <div style={{padding:'0 20px 16px', display:'flex', flexDirection:'column', gap:8}}>
                  {items.map((it,j)=>(
                    <label key={j} style={{display:'flex', gap:10, alignItems:'center', cursor:'pointer', fontSize:13, color:'var(--ink-3)'}}>
                      <input type="checkbox" style={{accentColor:'var(--accent-deep)', width:14, height:14}}/>
                      {it}
                    </label>
                  ))}
                </div>
              </div>
            ))}
          </div>
        </aside>

        {/* Products */}
        <main>
          <div style={{display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:24}}>
            <span style={{fontSize:13, color:'var(--ink-4)'}}>عرض <strong style={{color:'var(--ink)'}}>{products.length}</strong> من {count} منتج</span>
            <div style={{display:'flex', gap:8, alignItems:'center'}}>
              <select value={sortBy} onChange={e=>setSortBy(e.target.value)} style={{
                padding:'9px 14px', border:'1px solid var(--rule)', borderRadius:'var(--r-1)',
                background:'var(--surface)', fontFamily:'var(--f-ar)', fontSize:13, color:'var(--ink)', cursor:'pointer'
              }}>
                {['الأكثر صلة','السعر: من الأقل','السعر: من الأعلى','الأحدث','الأعلى تقييماً'].map((o,i)=><option key={i}>{o}</option>)}
              </select>
              <div style={{display:'flex', border:'1px solid var(--rule)', borderRadius:'var(--r-1)', overflow:'hidden'}}>
                <button style={{padding:'9px 12px', background:'var(--indigo)', color:'#fff', border:'none', cursor:'pointer', fontSize:13}}>⊞</button>
                <button style={{padding:'9px 12px', background:'transparent', color:'var(--ink-4)', border:'none', borderRight:'1px solid var(--rule)', cursor:'pointer', fontSize:13}}>≡</button>
              </div>
            </div>
          </div>

          <div style={{display:'grid', gridTemplateColumns:'repeat(3,1fr)', gap:20}}>
            {products.map((p,i)=><ProductCard key={i} {...p}/>)}
          </div>

          {/* Pagination */}
          <div style={{display:'flex', justifyContent:'center', gap:8, marginTop:48}}>
            {['←', '1','2','3','...','8', '→'].map((p,i)=>(
              <button key={i} style={{
                width:38, height:38, display:'flex', alignItems:'center', justifyContent:'center',
                border:'1px solid var(--rule)', borderRadius:'var(--r-1)', cursor:'pointer',
                background: p==='1'?'var(--indigo)':'var(--surface)',
                color: p==='1'?'#fff':'var(--ink-3)',
                fontFamily:'var(--f-mono)', fontSize:13
              }}>{p}</button>
            ))}
          </div>
        </main>
      </div>
      <Footer/>
    </div>
  );
};
window.Category = Category;
