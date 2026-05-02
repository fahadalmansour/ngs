// NovaKeys Store — Homepage
// Reuses all tokens from tokens.css (--bg, --surface, --indigo, --accent, fonts, .btn, .chip, .card)
// Layout: likecard-style marketplace — category tabs, denomination pills, instant badges

// SVG icon system — no emoji
const ICONS = {
  all: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8} style={{width:14,height:14}}>
      <rect x="2" y="6" width="20" height="12" rx="4"/>
      <circle cx="8" cy="12" r="1.5" fill="currentColor" stroke="none"/>
      <line x1="16" y1="9" x2="16" y2="15"/>
      <line x1="13" y1="12" x2="19" y2="12"/>
    </svg>
  ),
  ps: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8} style={{width:14,height:14}}>
      <path d="M9 3v14l3 1V7c0-1.1.9-2 2-2s2 .9 2 2v1.5c0 1.1-.9 2-2 2H9"/>
      <path d="M4 18l5 2 11-3"/>
    </svg>
  ),
  xbox: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8} style={{width:14,height:14}}>
      <circle cx="12" cy="12" r="9"/>
      <path d="M7 7l10 10M17 7L7 17"/>
    </svg>
  ),
  steam: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8} style={{width:14,height:14}}>
      <rect x="3" y="4" width="18" height="14" rx="2"/>
      <path d="M7 8h10M7 12h7M7 16h5"/>
    </svg>
  ),
  apple: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8} style={{width:14,height:14}}>
      <path d="M12 4a3 3 0 0 0-3 3v.5C6.4 8.3 5 10.5 5 13c0 3.3 2.7 6 6 6h2c3.3 0 6-2.7 6-6 0-2.5-1.4-4.7-3-5.5V7a3 3 0 0 0-3-3z"/>
      <path d="M12 3V2" strokeLinecap="round"/>
    </svg>
  ),
  google: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8} style={{width:14,height:14}}>
      <polygon points="5,3 19,12 5,21" strokeLinejoin="round"/>
    </svg>
  ),
  games: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8} style={{width:14,height:14}}>
      <path d="M12 2L22 12 12 22 2 12Z" strokeLinejoin="round"/>
    </svg>
  ),
  software: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8} style={{width:14,height:14}}>
      <circle cx="8" cy="8" r="4"/>
      <path d="M12 8h8M16 5v6"/>
    </svg>
  ),
  bolt: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8} style={{width:20,height:20}}>
      <path d="M13 2L4 14h8l-1 8 9-12h-8l1-8z" strokeLinejoin="round"/>
    </svg>
  ),
  check: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8} style={{width:20,height:20}}>
      <circle cx="12" cy="12" r="9"/>
      <path d="M8 12l3 3 5-5" strokeLinecap="round" strokeLinejoin="round"/>
    </svg>
  ),
  chat: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8} style={{width:20,height:20}}>
      <path d="M20 2H4C2.9 2 2 2.9 2 4v12c0 1.1.9 2 2 2h14l4 4V4c0-1.1-.9-2-2-2z" strokeLinejoin="round"/>
    </svg>
  ),
  lock: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8} style={{width:20,height:20}}>
      <rect x="5" y="11" width="14" height="10" rx="2"/>
      <path d="M8 11V7a4 4 0 0 1 8 0v4"/>
    </svg>
  ),
};

const Icon = ({name}) => ICONS[name] || null;

const CATEGORIES = [
  { id:'all',     icon:'all',      label:'الكل' },
  { id:'ps',      icon:'ps',       label:'PlayStation' },
  { id:'xbox',    icon:'xbox',     label:'Xbox' },
  { id:'steam',   icon:'steam',    label:'Steam' },
  { id:'apple',   icon:'apple',    label:'Apple' },
  { id:'google',  icon:'google',   label:'Google Play' },
  { id:'games',   icon:'games',    label:'بطاقات الألعاب' },
  { id:'software',icon:'software', label:'البرامج' },
];

const PRODUCTS = [
  {
    id:1, cat:'ps', brand:'PlayStation', brandColor:'#003087',
    name:'PS Plus Essential', region:'KSA',
    denominations:[{label:'شهر', price:29},{ label:'٣ أشهر',price:69},{label:'سنة',price:199}],
    logo:'https://upload.wikimedia.org/wikipedia/commons/0/00/PlayStation_logo.svg',
  },
  {
    id:2, cat:'ps', brand:'PlayStation', brandColor:'#003087',
    name:'PS Plus Extra', region:'KSA',
    denominations:[{label:'شهر',price:55},{label:'٣ أشهر',price:139},{label:'سنة',price:379}],
    logo:'https://upload.wikimedia.org/wikipedia/commons/0/00/PlayStation_logo.svg',
  },
  {
    id:3, cat:'xbox', brand:'Xbox', brandColor:'#107C10',
    name:'Xbox Game Pass Ultimate', region:'KSA',
    denominations:[{label:'شهر',price:48},{label:'٣ أشهر',price:130},{label:'سنة',price:469}],
    logo:'https://upload.wikimedia.org/wikipedia/commons/f/f9/Xbox_one_logo.svg',
  },
  {
    id:4, cat:'steam', brand:'Steam', brandColor:'#1b2838',
    name:'Steam Gift Card', region:'Global',
    denominations:[{label:'٢٠$',price:75},{label:'٥٠$',price:188},{label:'١٠٠$',price:375}],
    logo:'https://upload.wikimedia.org/wikipedia/commons/8/83/Steam_icon_logo.svg',
  },
  {
    id:5, cat:'apple', brand:'Apple', brandColor:'#555',
    name:'App Store & iTunes', region:'KSA',
    denominations:[{label:'٢٥ ريال',price:25},{label:'٥٠ ريال',price:50},{label:'١٠٠ ريال',price:100}],
    logo:'https://upload.wikimedia.org/wikipedia/commons/f/fa/Apple_logo_black.svg',
  },
  {
    id:6, cat:'games', brand:'Fortnite', brandColor:'#9b59b6',
    name:'Fortnite V-Bucks', region:'Global',
    denominations:[{label:'١٠٠٠',price:32},{label:'٢٨٠٠',price:75},{label:'٥٠٠٠',price:132}],
    logo:null,
  },
  {
    id:7, cat:'games', brand:'PUBG', brandColor:'#f39c12',
    name:'PUBG UC', region:'Global',
    denominations:[{label:'٣٢٥ UC',price:22},{label:'٦٦٠ UC',price:43},{label:'١٨٠٠ UC',price:105}],
    logo:null,
  },
  {
    id:8, cat:'software', brand:'Microsoft', brandColor:'#0078d4',
    name:'Windows 11 Pro Key', region:'Global',
    denominations:[{label:'مفتاح واحد',price:199}],
    logo:null,
  },
];

const REGIONS = ['الكل','KSA','UAE','US','UK','Global'];

// ── Sub-components ──────────────────────────────────────────────────────────

const InstantBadge = () => (
  <span style={{
    display:'inline-flex', alignItems:'center', gap:4,
    background:'rgba(34,197,94,0.12)', color:'#16a34a',
    border:'1px solid rgba(34,197,94,0.25)',
    borderRadius:'var(--r-pill)', padding:'2px 8px',
    fontSize:10, fontFamily:'var(--f-ar)', fontWeight:600, letterSpacing:'0.01em'
  }}>
    <span style={{width:5,height:5,borderRadius:'50%',background:'#16a34a',display:'inline-block'}}></span>
    تسليم فوري
  </span>
);

const DenominationPill = ({label, price, selected, onClick}) => (
  <button onClick={onClick} style={{
    padding:'4px 10px', borderRadius:'var(--r-pill)', cursor:'pointer',
    fontFamily:'var(--f-ar)', fontSize:12, fontWeight:selected?700:400,
    border: selected ? '1.5px solid var(--indigo)' : '1px solid var(--rule)',
    background: selected ? 'var(--indigo)' : 'var(--surface)',
    color: selected ? '#fff' : 'var(--ink-3)',
    transition:'all 0.15s'
  }}>
    {label} · {price} ر.س
  </button>
);

const ProductCard = ({product}) => {
  const [selDen, setSelDen] = React.useState(0);
  const den = product.denominations[selDen];
  return (
    <div className="card" style={{
      display:'flex', flexDirection:'column', gap:14,
      borderRadius:'var(--r-3)', padding:20, background:'var(--surface)',
      border:'1px solid var(--rule)', transition:'box-shadow 0.2s',
    }}
    onMouseEnter={e=>e.currentTarget.style.boxShadow='var(--shadow-lg)'}
    onMouseLeave={e=>e.currentTarget.style.boxShadow='none'}
    >
      <div style={{display:'flex', alignItems:'center', justifyContent:'space-between'}}>
        <div style={{display:'flex', alignItems:'center', gap:8}}>
          <span style={{
            width:36, height:36, borderRadius:'var(--r-1)',
            background: product.brandColor + '18',
            display:'flex', alignItems:'center', justifyContent:'center',
            fontSize:20
          }}>
            {product.logo
              ? <img src={product.logo} style={{width:20,height:20,objectFit:'contain'}} alt={product.brand}/>
              : product.brand.charAt(0)
            }
          </span>
          <div>
            <div style={{fontFamily:'var(--f-ar)', fontSize:13, fontWeight:700, color:'var(--indigo)'}}>{product.name}</div>
            <div style={{fontFamily:'var(--f-mono)', fontSize:10, color:'var(--dim)', textTransform:'uppercase'}}>{product.region}</div>
          </div>
        </div>
        <InstantBadge/>
      </div>

      <div style={{display:'flex', flexWrap:'wrap', gap:6}}>
        {product.denominations.map((d,i)=>(
          <DenominationPill key={i} label={d.label} price={d.price} selected={selDen===i} onClick={()=>setSelDen(i)}/>
        ))}
      </div>

      <div style={{display:'flex', alignItems:'center', justifyContent:'space-between', marginTop:'auto'}}>
        <div>
          <span className="price" style={{fontSize:22, fontWeight:700, color:'var(--indigo)', fontFamily:'var(--f-wordmark)'}}>{den.price}</span>
          <span style={{fontFamily:'var(--f-ar)', fontSize:12, color:'var(--ink-4)', marginRight:4}}>ر.س</span>
          <div style={{fontFamily:'var(--f-ar)', fontSize:10, color:'var(--dim)'}}>شامل ضريبة القيمة المضافة</div>
        </div>
        <button className="btn" style={{padding:'8px 18px', fontSize:13, borderRadius:'var(--r-2)'}}>
          اشتري الآن
        </button>
      </div>
    </div>
  );
};

const TrustBar = () => (
  <div style={{
    display:'grid', gridTemplateColumns:'repeat(4,1fr)', gap:1,
    border:'1px solid var(--rule)', borderRadius:'var(--r-2)',
    overflow:'hidden', background:'var(--rule)'
  }}>
    {[
      {icon:'bolt',  title:'تسليم فوري',    sub:'خلال ٦٠ ثانية من الدفع'},
      {icon:'check', title:'أكواد موثوقة',   sub:'مضمونة أو الاستبدال خلال ٢٤ ساعة'},
      {icon:'chat',  title:'دعم ٢٤ ساعة',   sub:'واتساب: 0570131122'},
      {icon:'lock',  title:'دفع آمن',        sub:'مدى · فيزا · Apple Pay'},
    ].map((t,i)=>(
      <div key={i} style={{
        background:'var(--surface)', padding:'18px 20px',
        display:'flex', flexDirection:'column', gap:4
      }}>
        <span style={{color:'var(--indigo)'}}><Icon name={t.icon}/></span>
        <div style={{fontFamily:'var(--f-ar)', fontWeight:700, fontSize:14, color:'var(--indigo)'}}>{t.title}</div>
        <div style={{fontFamily:'var(--f-ar)', fontSize:12, color:'var(--ink-4)'}}>{t.sub}</div>
      </div>
    ))}
  </div>
);

const CategoryTabs = ({active, onChange}) => (
  <div style={{
    display:'flex', gap:4, overflowX:'auto', paddingBottom:2,
    scrollbarWidth:'none', WebkitScrollbarDisplay:'none'
  }}>
    {CATEGORIES.map(cat=>(
      <button key={cat.id} onClick={()=>onChange(cat.id)}
        className={active===cat.id ? 'chip chip-active' : 'chip'}
        style={{
          whiteSpace:'nowrap', cursor:'pointer',
          fontFamily:'var(--f-ar)', fontSize:13,
          padding:'7px 16px', borderRadius:'var(--r-pill)',
          border: active===cat.id ? '1.5px solid var(--indigo)' : '1px solid var(--rule)',
          background: active===cat.id ? 'var(--indigo)' : 'var(--surface)',
          color: active===cat.id ? '#fff' : 'var(--ink-3)',
          display:'flex', alignItems:'center', gap:6
        }}>
        <Icon name={cat.icon}/> {cat.label}
      </button>
    ))}
  </div>
);

const LiveCounter = () => {
  const [count, setCount] = React.useState(1847);
  React.useEffect(()=>{
    const t = setInterval(()=>setCount(c=>c + Math.floor(Math.random()*3)), 8000);
    return ()=>clearInterval(t);
  },[]);
  return (
    <span style={{
      fontFamily:'var(--f-mono)', fontWeight:700,
      color:'var(--accent)', fontSize:'inherit'
    }}>{count.toLocaleString('ar-SA')}</span>
  );
};

// ── Main Component ──────────────────────────────────────────────────────────

const NovaKeysHome = () => {
  const [activeCat, setActiveCat] = React.useState('all');
  const [activeRegion, setActiveRegion] = React.useState('الكل');

  const filtered = PRODUCTS.filter(p=>{
    const catOk = activeCat==='all' || p.cat===activeCat;
    const regOk = activeRegion==='الكل' || p.region===activeRegion || p.region==='Global';
    return catOk && regOk;
  });

  return (
    <div dir="rtl" style={{background:'var(--bg)', color:'var(--ink)', minHeight:'100%', fontFamily:'var(--f-ar)'}}>

      {/* HEADER */}
      <header style={{
        position:'sticky', top:0, zIndex:100,
        borderBottom:'1px solid var(--rule)',
        background:'rgba(248,250,252,0.92)', backdropFilter:'blur(12px)',
        padding:'0 48px'
      }}>
        <div style={{maxWidth:1200, margin:'0 auto', height:60, display:'flex', alignItems:'center', justifyContent:'space-between'}}>
          <div style={{display:'flex', alignItems:'center', gap:10}}>
            <span style={{
              fontFamily:'var(--f-wordmark)', fontWeight:700, fontSize:22,
              color:'var(--indigo)', letterSpacing:'-0.02em'
            }}>NovaKeys</span>
            <span style={{
              fontFamily:'var(--f-mono)', fontSize:9, color:'var(--dim)',
              textTransform:'uppercase', letterSpacing:'0.1em',
              border:'1px solid var(--rule)', borderRadius:'var(--r-pill)', padding:'2px 6px'
            }}>STORE</span>
          </div>
          <div style={{display:'flex', gap:8}}>
            <button className="btn btn-ghost" style={{fontSize:13, padding:'6px 14px'}}>الطلبات</button>
            <button className="btn" style={{fontSize:13, padding:'6px 14px'}}>تسجيل الدخول</button>
          </div>
        </div>
      </header>

      {/* HERO */}
      <section style={{
        borderBottom:'1px solid var(--rule)',
        background:`
          radial-gradient(800px circle at 60% 50%, rgba(56,189,248,0.09), transparent 60%),
          linear-gradient(rgba(10,10,10,0.03) 1px, transparent 1px),
          linear-gradient(90deg, rgba(10,10,10,0.03) 1px, transparent 1px),
          var(--bg)`,
        backgroundSize:'auto, 48px 48px, 48px 48px, auto',
        padding:'72px 48px 64px'
      }}>
        <div style={{maxWidth:1200, margin:'0 auto', textAlign:'center'}}>
          <div style={{
            display:'inline-flex', alignItems:'center', gap:8, marginBottom:24,
            padding:'5px 14px', border:'1px solid var(--rule-strong)', borderRadius:'var(--r-pill)',
            fontFamily:'var(--f-mono)', fontSize:11, color:'var(--ink-4)', textTransform:'uppercase', letterSpacing:'0.08em'
          }}>
            <span style={{width:6,height:6,borderRadius:'50%',background:'#16a34a',display:'inline-block',boxShadow:'0 0 0 2px rgba(34,197,94,0.25)'}}></span>
            مفاتيح رقمية من المملكة لكل الخليج
          </div>

          <h1 style={{
            fontFamily:'var(--f-wordmark)', fontWeight:700,
            fontSize:'clamp(40px,6vw,80px)', lineHeight:0.95,
            letterSpacing:'-0.03em', margin:'0 0 16px', color:'var(--indigo)'
          }}>
            مفاتيح رقمية.
            <br/>
            <span style={{color:'var(--accent)', fontStyle:'italic', fontWeight:400}}>تسليم فوري</span>
            {' '}خلال ٦٠ ثانية.
          </h1>

          <p style={{
            fontFamily:'var(--f-ar)', fontSize:17, lineHeight:1.7,
            color:'var(--ink-3)', maxWidth:560, margin:'0 auto 32px'
          }}>
            بلايستيشن · إكس بوكس · ستيم · آبل · جوجل بلاي · ألعاب · برامج
            <br/>
            اشحن رصيدك الآن من المملكة العربية السعودية
          </p>

          <div style={{
            display:'inline-flex', alignItems:'center', gap:8,
            fontFamily:'var(--f-ar)', fontSize:15, color:'var(--ink-4)'
          }}>
            <LiveCounter/>
            <span>كود تم تسليمه هذا الشهر</span>
          </div>

          <div style={{display:'flex', gap:10, justifyContent:'center', marginTop:28}}>
            <button className="btn" style={{padding:'12px 28px', fontSize:15, borderRadius:'var(--r-2)'}}>
              تصفّح البطاقات
            </button>
            <button className="btn btn-ghost" style={{padding:'12px 20px', fontSize:15, borderRadius:'var(--r-2)'}}>
              تتبع طلبك
            </button>
          </div>
        </div>
      </section>

      {/* TRUST BAR */}
      <section style={{padding:'32px 48px', borderBottom:'1px solid var(--rule)'}}>
        <div style={{maxWidth:1200, margin:'0 auto'}}>
          <TrustBar/>
        </div>
      </section>

      {/* CATALOG */}
      <section style={{padding:'40px 48px 80px'}}>
        <div style={{maxWidth:1200, margin:'0 auto'}}>

          <div style={{
            position:'sticky', top:61, zIndex:50,
            background:'var(--bg)', paddingBottom:16, marginBottom:8,
            borderBottom:'1px solid var(--rule)'
          }}>
            <div style={{display:'flex', gap:16, alignItems:'center', flexWrap:'wrap'}}>
              <CategoryTabs active={activeCat} onChange={setActiveCat}/>
              <div style={{display:'flex', gap:4, marginRight:'auto'}}>
                {REGIONS.map(r=>(
                  <button key={r} onClick={()=>setActiveRegion(r)}
                    style={{
                      padding:'5px 12px', borderRadius:'var(--r-pill)', cursor:'pointer',
                      fontFamily:'var(--f-ar)', fontSize:12,
                      border: activeRegion===r ? '1.5px solid var(--accent)' : '1px solid var(--rule)',
                      background: activeRegion===r ? 'rgba(56,189,248,0.1)' : 'var(--surface)',
                      color: activeRegion===r ? 'var(--accent)' : 'var(--ink-4)',
                    }}>
                    {r}
                  </button>
                ))}
              </div>
            </div>
          </div>

          <div style={{display:'flex', alignItems:'baseline', gap:12, margin:'24px 0 20px'}}>
            <h2 style={{fontFamily:'var(--f-ar)', fontWeight:700, fontSize:22, color:'var(--indigo)', margin:0}}>
              {activeCat==='all' ? 'جميع البطاقات' : CATEGORIES.find(c=>c.id===activeCat)?.label}
            </h2>
            <span style={{fontFamily:'var(--f-mono)', fontSize:12, color:'var(--dim)'}}>
              {filtered.length} منتج
            </span>
          </div>

          {filtered.length > 0 ? (
            <div style={{
              display:'grid',
              gridTemplateColumns:'repeat(auto-fill, minmax(280px, 1fr))',
              gap:20
            }}>
              {filtered.map(p=><ProductCard key={p.id} product={p}/>)}
            </div>
          ) : (
            <div style={{
              textAlign:'center', padding:'60px 0',
              fontFamily:'var(--f-ar)', color:'var(--dim)', fontSize:15
            }}>
              لا توجد منتجات في هذه الفئة حالياً
            </div>
          )}
        </div>
      </section>

      {/* FOOTER */}
      <footer style={{
        borderTop:'1px solid var(--rule)', padding:'32px 48px',
        background:'var(--surface)'
      }}>
        <div style={{
          maxWidth:1200, margin:'0 auto',
          display:'flex', justifyContent:'space-between', alignItems:'center',
          flexWrap:'wrap', gap:16
        }}>
          <div>
            <div style={{fontFamily:'var(--f-wordmark)', fontWeight:700, fontSize:18, color:'var(--indigo)'}}>NovaKeys</div>
            <div style={{fontFamily:'var(--f-ar)', fontSize:12, color:'var(--dim)', marginTop:4}}>
              س.ت 7053130576 · ض.ق.م 3145127947 · الرياض، المملكة العربية السعودية
            </div>
          </div>
          <div style={{display:'flex', gap:24}}>
            {['الشروط والأحكام','سياسة الخصوصية','سياسة الإرجاع','تواصل معنا'].map(l=>(
              <a key={l} href="#" style={{
                fontFamily:'var(--f-ar)', fontSize:13, color:'var(--ink-4)',
                textDecoration:'none'
              }}>{l}</a>
            ))}
          </div>
        </div>
      </footer>

    </div>
  );
};

export default NovaKeysHome;
