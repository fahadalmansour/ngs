// Admin Dashboard
const AdminDashboard = () => {
  const [period, setPeriod] = React.useState('7d');

  const stats = [
    {label:'المبيعات', sub:'آخر 7 أيام', value:'124,850', unit:'SAR', delta:'+18.4%', up:true},
    {label:'الطلبات', sub:'آخر 7 أيام', value:'247', unit:'طلب', delta:'+12.1%', up:true},
    {label:'متوسط الطلب', sub:'آخر 7 أيام', value:'505', unit:'SAR', delta:'+5.3%', up:true},
    {label:'معدل الإرجاع', sub:'آخر 30 يوم', value:'2.1', unit:'%', delta:'-0.4%', up:true},
    {label:'منتجات منخفضة', sub:'أقل من 5 وحدات', value:'12', unit:'منتج', delta:'+3', up:false},
    {label:'تذاكر دعم مفتوحة', sub:'بانتظار الرد', value:'7', unit:'تذكرة', delta:'-2', up:true},
  ];

  const recentOrders = [
    {id:'NG-2026-04725', customer:'فيصل المطيري', items:2, total:3840, status:'جديد', statusC:'var(--accent-deep)', time:'منذ ٥ دقائق'},
    {id:'NG-2026-04724', customer:'نورة السبيعي', items:1, total:890, status:'قيد التجهيز', statusC:'var(--warn)', time:'منذ ٢٣ دقيقة'},
    {id:'NG-2026-04723', customer:'خالد العنزي', items:4, total:7290, status:'تم الشحن', statusC:'var(--good)', time:'منذ ساعة'},
    {id:'NG-2026-04722', customer:'سارة الدوسري', items:1, total:399, status:'تم التسليم', statusC:'var(--good)', time:'منذ ٣ ساعات'},
    {id:'NG-2026-04721', customer:'أحمد السبيعي', items:3, total:5080, status:'تم التسليم', statusC:'var(--good)', time:'أمس'},
  ];

  const topProducts = [
    {sku:'NG-ENT-003', name:'Ubiquiti UDM-Pro', sold:18, revenue:36972, stock:12},
    {sku:'SH-HUB-AQRA-001', name:'Aqara Hub M3', sold:31, revenue:27869, stock:28},
    {sku:'NT-WAP-TPL-002', name:'TP-Link EAP650', sold:44, revenue:35156, stock:5},
    {sku:'GC-PSP-KSA-12', name:'PS Plus 12M KSA', sold:67, revenue:26733, stock:999},
    {sku:'GM-STR-ELG-001', name:'Elgato Stream Deck', sold:22, revenue:20878, stock:9},
  ];

  const barH = [42,58,65,49,73,87,95];
  const days = ['السبت','الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس','الجمعة'];

  return (
    <div dir="rtl" style={{background:'var(--bg)', minHeight:'100%', fontFamily:'var(--f-ar)'}}>
      {/* Admin top bar */}
      <div style={{background:'var(--indigo-deep)', color:'rgba(255,255,255,0.8)', padding:'12px 32px', display:'flex', justifyContent:'space-between', alignItems:'center'}}>
        <div style={{display:'flex', gap:12, alignItems:'center'}}>
          <img src="assets/ng-mark.png" alt="NG" style={{height:24, filter:'brightness(0) invert(1)'}}/>
          <span style={{fontFamily:'var(--f-wordmark)', fontWeight:700, color:'#fff', fontSize:14}}>NEOGEN</span>
          <span className="mono-up" style={{color:'rgba(255,255,255,0.4)', fontSize:10}}>· لوحة الإدارة</span>
        </div>
        <div style={{display:'flex', gap:20, fontSize:13}}>
          {['الطلبات','المنتجات','العملاء','التقارير','الإعدادات'].map((m,i)=>(
            <a key={i} href="#" style={{color:i===0?'var(--accent)':'rgba(255,255,255,0.6)'}}>{m}</a>
          ))}
        </div>
        <div style={{display:'flex', gap:10, alignItems:'center'}}>
          <div className="mono-up" style={{color:'var(--accent)', fontSize:9}}>7 تذاكر مفتوحة ●</div>
          <div style={{width:32, height:32, borderRadius:'50%', background:'var(--accent)', display:'grid', placeItems:'center', fontFamily:'var(--f-wordmark)', fontWeight:700, fontSize:13, color:'var(--indigo)'}}>F</div>
        </div>
      </div>

      <div style={{maxWidth:1440, margin:'0 auto', padding:'32px 40px 80px'}}>
        {/* Page header */}
        <div style={{display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:28}}>
          <div>
            <h1 style={{margin:0, fontSize:26, fontWeight:700, color:'var(--indigo)'}}>لوحة التحكم</h1>
            <div className="mono" style={{fontSize:12, color:'var(--dim)', marginTop:4}}>1 مايو 2026 · الرياض · KSA</div>
          </div>
          <div style={{display:'flex', gap:8, alignItems:'center'}}>
            <div style={{display:'flex', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', overflow:'hidden'}}>
              {[['7d','7 أيام'],['30d','30 يوم'],['90d','3 أشهر'],['1y','سنة']].map(([k,l],i)=>(
                <button key={i} onClick={()=>setPeriod(k)} style={{
                  padding:'8px 14px', fontFamily:'var(--f-ar)', fontSize:12, fontWeight:500,
                  background:period===k?'var(--indigo)':'var(--surface)',
                  color:period===k?'#fff':'var(--ink-3)',
                  border:'none', cursor:'pointer'
                }}>{l}</button>
              ))}
            </div>
            <button className="btn btn-sm" style={{borderRadius:'var(--r-2)'}}>+ طلب جديد</button>
          </div>
        </div>

        {/* KPI cards */}
        <div style={{display:'grid', gridTemplateColumns:'repeat(6,1fr)', gap:12, marginBottom:24}}>
          {stats.map((s,i)=>(
            <div key={i} style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', padding:'16px 18px', boxShadow:'var(--shadow-sm)'}}>
              <div className="mono-up" style={{color:'var(--dim)', fontSize:9, marginBottom:6}}>{s.label}</div>
              <div style={{display:'flex', alignItems:'baseline', gap:4}}>
                <span style={{fontFamily:'var(--f-wordmark)', fontSize:24, fontWeight:700, color:'var(--indigo)', lineHeight:1}}>{s.value}</span>
                <span className="mono-up" style={{color:'var(--dim)', fontSize:9}}>{s.unit}</span>
              </div>
              <div style={{display:'flex', justifyContent:'space-between', marginTop:8}}>
                <span className="mono" style={{fontSize:10, color:'var(--dim)'}}>{s.sub}</span>
                <span style={{fontFamily:'var(--f-wordmark)', fontSize:11, fontWeight:600, color:s.up?'var(--good)':'var(--sale)'}}>{s.delta}</span>
              </div>
            </div>
          ))}
        </div>

        <div style={{display:'grid', gridTemplateColumns:'2fr 1fr', gap:20, marginBottom:20}}>
          {/* Revenue chart */}
          <div style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', padding:24, boxShadow:'var(--shadow-sm)'}}>
            <div style={{display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:24}}>
              <div>
                <div className="mono-up" style={{color:'var(--dim)', fontSize:9, marginBottom:4}}>المبيعات اليومية</div>
                <div style={{fontFamily:'var(--f-wordmark)', fontWeight:700, fontSize:20, color:'var(--indigo)'}}>124,850 SAR</div>
              </div>
              <span style={{fontFamily:'var(--f-wordmark)', fontSize:13, fontWeight:600, color:'var(--good)'}}>↑ +18.4% الأسبوع الماضي</span>
            </div>
            {/* Bar chart */}
            <div style={{display:'flex', gap:8, alignItems:'flex-end', height:140}}>
              {barH.map((h,i)=>(
                <div key={i} style={{flex:1, display:'flex', flexDirection:'column', alignItems:'center', gap:6, height:'100%'}}>
                  <div style={{
                    width:'100%', background: i===6?'var(--accent)':'var(--surface-2)',
                    borderRadius:'var(--r-1) var(--r-1) 0 0',
                    height:`${h}%`, marginTop:'auto',
                    border: i===6?'none':'1px solid var(--rule)',
                    transition:'height .3s var(--ease-out)'
                  }}/>
                  <span className="mono-up" style={{fontSize:8, color: i===6?'var(--accent-deep)':'var(--dim)'}}>{days[i]}</span>
                </div>
              ))}
            </div>
          </div>

          {/* Category breakdown */}
          <div style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', padding:24, boxShadow:'var(--shadow-sm)'}}>
            <div className="mono-up" style={{color:'var(--dim)', fontSize:9, marginBottom:16}}>المبيعات حسب الفئة</div>
            <div style={{display:'flex', flexDirection:'column', gap:12}}>
              {[
                ['بطاقات رقمية',38,'var(--accent)'],
                ['البيت الذكي',24,'var(--good)'],
                ['الشبكات',19,'var(--indigo)'],
                ['الألعاب',12,'var(--warn)'],
                ['أخرى',7,'var(--dim)'],
              ].map(([cat,pct,col],i)=>(
                <div key={i}>
                  <div style={{display:'flex', justifyContent:'space-between', marginBottom:5}}>
                    <span style={{fontSize:13, color:'var(--ink-2)'}}>{cat}</span>
                    <span style={{fontFamily:'var(--f-wordmark)', fontWeight:600, fontSize:13, color:col}}>{pct}%</span>
                  </div>
                  <div style={{height:6, background:'var(--surface-2)', borderRadius:3}}>
                    <div style={{width:`${pct}%`, height:'100%', background:col, borderRadius:3}}/>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>

        <div style={{display:'grid', gridTemplateColumns:'1.3fr 1fr', gap:20}}>
          {/* Recent orders */}
          <div style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', overflow:'hidden', boxShadow:'var(--shadow-sm)'}}>
            <div style={{padding:'14px 20px', borderBottom:'1px solid var(--rule)', display:'flex', justifyContent:'space-between', background:'var(--surface-2)'}}>
              <span className="mono-up" style={{color:'var(--ink-4)'}}>أحدث الطلبات</span>
              <a href="#" style={{fontSize:12, color:'var(--accent-deep)', fontWeight:600}}>عرض الكل →</a>
            </div>
            {recentOrders.map((o,i)=>(
              <div key={i} style={{
                display:'grid', gridTemplateColumns:'1fr auto auto auto auto', gap:16,
                padding:'12px 20px', borderBottom:i<4?'1px solid var(--rule)':'none',
                alignItems:'center'
              }}>
                <div>
                  <div className="mono" style={{fontSize:11, color:'var(--accent-deep)', fontWeight:600}}>{o.id}</div>
                  <div style={{fontSize:13, color:'var(--ink-2)', marginTop:2}}>{o.customer}</div>
                </div>
                <span className="mono-up" style={{fontSize:9, color:'var(--dim)'}}>{o.items} منتجات</span>
                <span style={{fontFamily:'var(--f-wordmark)', fontSize:13, fontWeight:600, color:'var(--indigo)'}}>{o.total.toLocaleString('en-US')}</span>
                <span style={{fontFamily:'var(--f-mono)', fontSize:10, color:o.statusC}}>● {o.status}</span>
                <span className="mono" style={{fontSize:10, color:'var(--dim)', whiteSpace:'nowrap'}}>{o.time}</span>
              </div>
            ))}
          </div>

          {/* Top products */}
          <div style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', overflow:'hidden', boxShadow:'var(--shadow-sm)'}}>
            <div style={{padding:'14px 20px', borderBottom:'1px solid var(--rule)', display:'flex', justifyContent:'space-between', background:'var(--surface-2)'}}>
              <span className="mono-up" style={{color:'var(--ink-4)'}}>الأكثر مبيعاً</span>
              <a href="#" style={{fontSize:12, color:'var(--accent-deep)', fontWeight:600}}>المخزون →</a>
            </div>
            {topProducts.map((p,i)=>(
              <div key={i} style={{
                display:'grid', gridTemplateColumns:'1fr auto auto auto',
                gap:12, padding:'12px 20px',
                borderBottom:i<4?'1px solid var(--rule)':'none', alignItems:'center'
              }}>
                <div>
                  <div className="mono" style={{fontSize:10, color:'var(--dim)'}}>{p.sku}</div>
                  <div style={{fontSize:13, color:'var(--ink-2)', marginTop:2, lineHeight:1.3}}>{p.name}</div>
                </div>
                <div style={{textAlign:'center'}}>
                  <div style={{fontFamily:'var(--f-wordmark)', fontWeight:600, fontSize:14, color:'var(--indigo)'}}>{p.sold}</div>
                  <div className="mono-up" style={{fontSize:8, color:'var(--dim)'}}>مباع</div>
                </div>
                <div style={{textAlign:'center'}}>
                  <div style={{fontFamily:'var(--f-wordmark)', fontWeight:600, fontSize:12, color:'var(--ink-3)'}}>{p.revenue.toLocaleString('en-US')}</div>
                  <div className="mono-up" style={{fontSize:8, color:'var(--dim)'}}>SAR</div>
                </div>
                <div style={{textAlign:'center'}}>
                  <div style={{fontFamily:'var(--f-wordmark)', fontWeight:700, fontSize:14, color:p.stock<10?'var(--sale)':'var(--good)'}}>{p.stock}</div>
                  <div className="mono-up" style={{fontSize:8, color:p.stock<10?'var(--sale)':'var(--dim)'}}>مخزون</div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
};
window.AdminDashboard = AdminDashboard;
