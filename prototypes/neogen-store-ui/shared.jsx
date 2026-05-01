/* Shared components — NeoGen real brand system */

// Logo component using real NG mark asset
const Logo = ({inverted=false, size=36}) => (
  <div style={{display:'flex', alignItems:'center', gap:10}}>
    <img
      src="assets/ng-mark.png"
      alt="NeoGen"
      style={{height:size, width:'auto', filter: inverted ? 'brightness(0) invert(1)' : 'none'}}
    />
    <div>
      <div style={{
        fontFamily:'var(--f-wordmark)', fontWeight:700,
        fontSize: size*0.42, lineHeight:1, letterSpacing:'-0.01em',
        color: inverted ? '#fff' : 'var(--indigo)'
      }}>NEOGEN</div>
      <div style={{
        fontFamily:'var(--f-mono)', textTransform:'uppercase',
        letterSpacing:'0.08em', fontSize:size*0.22,
        color: inverted ? 'rgba(255,255,255,0.45)' : 'var(--ink-4)',
        marginTop:2
      }}>STORE · متجر</div>
    </div>
  </div>
);

// Top bar
const TopBar = () => (
  <div style={{
    background:'var(--indigo-deep)', color:'rgba(255,255,255,0.65)',
    fontFamily:'var(--f-mono)', fontSize:11, letterSpacing:'0.08em',
    textTransform:'uppercase'
  }}>
    <div style={{
      maxWidth:1440, margin:'0 auto', padding:'8px 32px',
      display:'flex', justifyContent:'space-between', alignItems:'center', gap:24
    }}>
      <div style={{display:'flex', gap:20, alignItems:'center'}}>
        <span><span className="dot dot-on" style={{marginInlineEnd:8}}></span>متوفر · في المملكة</span>
        <span style={{opacity:0.4}}>·</span>
        <span>شحن 2–5 أيام</span>
        <span style={{opacity:0.4}}>·</span>
        <span>إرجاع 14 يوم</span>
        <span style={{opacity:0.4}}>·</span>
        <span style={{color:'var(--accent)'}}>GCC · KSA · UAE · KW · BH · OM · QA</span>
      </div>
      <div style={{display:'flex', gap:16}}>
        <span>SAR ﷼</span>
        <span style={{opacity:0.4}}>|</span>
        <span>عربي / EN</span>
      </div>
    </div>
  </div>
);

// Header
const Header = () => {
  const links = [
    ['البيت الذكي','Smart Home'],
    ['الألعاب','Gaming'],
    ['هوم لاب','Homelab'],
    ['الشبكات','Networking'],
    ['الأجهزة','Hardware'],
    ['بطاقات رقمية','Gift Cards'],
  ];
  return (
    <header style={{
      borderBottom:'1px solid var(--rule)',
      background:'rgba(248,250,252,0.92)',
      backdropFilter:'blur(12px)',
      WebkitBackdropFilter:'blur(12px)',
      position:'sticky', top:0, zIndex:100
    }}>
      <div style={{
        maxWidth:1440, margin:'0 auto', padding:'16px 32px',
        display:'grid', gridTemplateColumns:'auto 1fr auto', alignItems:'center', gap:32
      }}>
        <Logo size={34}/>
        <nav style={{display:'flex', justifyContent:'center', gap:28, fontSize:14}}>
          {links.map(([ar,en],i) => (
            <a key={i} href="#" style={{
              color:'var(--ink-3)', textDecoration:'none',
              display:'flex', flexDirection:'column', alignItems:'center', gap:2,
              fontWeight:500, transition:'color .15s'
            }}>
              <span>{ar}</span>
              <span style={{fontFamily:'var(--f-mono)', fontSize:9, letterSpacing:'0.07em', textTransform:'uppercase', color:'var(--dim)'}}>{en}</span>
            </a>
          ))}
        </nav>
        <div style={{display:'flex', gap:8, alignItems:'center'}}>
          <button className="btn-ghost btn btn-sm" style={{borderRadius:'var(--r-2)'}}>
            <span style={{fontFamily:'var(--f-mono)', fontSize:11}}>⌘K</span> بحث
          </button>
          <button className="btn-ghost btn btn-sm" style={{borderRadius:'var(--r-2)'}}>الحساب</button>
          <button className="btn btn-dark btn-sm" style={{borderRadius:'var(--r-2)'}}>
            السلة
            <span style={{
              background:'var(--accent)', color:'var(--indigo-deep)',
              borderRadius:'999px', padding:'1px 7px',
              fontFamily:'var(--f-mono)', fontSize:11, fontWeight:700
            }}>02</span>
          </button>
        </div>
      </div>
    </header>
  );
};

// Footer
const Footer = () => (
  <footer style={{background:'var(--indigo-deep)', color:'rgba(255,255,255,0.8)', marginTop:80}}>
    <div style={{maxWidth:1440, margin:'0 auto', padding:'64px 32px'}}>
      <div style={{
        display:'grid', gridTemplateColumns:'1.5fr 1fr 1fr 1fr 1fr', gap:48,
        paddingBottom:40, borderBottom:'1px solid rgba(255,255,255,0.08)'
      }}>
        <div>
          <Logo inverted size={34}/>
          <p style={{fontSize:14, color:'rgba(255,255,255,0.5)', lineHeight:1.65, maxWidth:280, marginTop:20}}>
            متجر تقني سعودي لمحترفي الشبكات، الهوم لاب، البيوت الذكية، والألعاب. نغطي كل دول الخليج.
          </p>
          <div style={{display:'flex', gap:8, marginTop:20, flexWrap:'wrap'}}>
            {['🇸🇦','🇦🇪','🇰🇼','🇧🇭','🇴🇲','🇶🇦'].map((f,i)=>(
              <span key={i} style={{fontSize:18}}>{f}</span>
            ))}
          </div>
          <div style={{fontFamily:'var(--f-mono)', fontSize:10, textTransform:'uppercase', letterSpacing:'0.08em', color:'rgba(255,255,255,0.25)', marginTop:20}}>
            س.ت 7053130576 · ضريبة 15%
          </div>
        </div>
        {[
          ['الفئات',['البيت الذكي','الألعاب','هوم لاب','الشبكات','الأجهزة','بطاقات رقمية']],
          ['الخدمة',['ابنِ جهازك','شبكة مكتبية','هوم لاب','بيت ذكي','تواصل']],
          ['المتجر',['الشحن والتسليم','الإرجاع','الضمان','طرق الدفع','الأسئلة']],
          ['تواصل',['واتساب: 0570131122','support@neogen.store','الرياض، المملكة','9ص–9م يومياً','TikTok: @neogen.store']],
        ].map(([title,items],i)=>(
          <div key={i}>
            <div style={{fontFamily:'var(--f-mono)', fontSize:10, textTransform:'uppercase', letterSpacing:'0.1em', color:'rgba(255,255,255,0.35)', marginBottom:16}}>{title}</div>
            <ul style={{listStyle:'none', padding:0, margin:0, display:'flex', flexDirection:'column', gap:10}}>
              {items.map((it,j)=>(
                <li key={j} style={{fontSize:14, color:'rgba(255,255,255,0.6)'}}>{it}</li>
              ))}
            </ul>
          </div>
        ))}
      </div>
      <div style={{display:'flex', justifyContent:'space-between', alignItems:'center', paddingTop:24, fontSize:11, color:'rgba(255,255,255,0.25)', fontFamily:'var(--f-mono)'}}>
        <span>© 2026 NEOGEN STORE · جميع الحقوق محفوظة</span>
        <div style={{display:'flex', alignItems:'center', gap:16}}>
          <a href="https://tiktok.com/@neogen.store" target="_blank" rel="noopener"
             style={{display:'flex', alignItems:'center', gap:6, color:'rgba(255,255,255,0.45)', textDecoration:'none', transition:'color .15s'}}
             title="TikTok @neogen.store">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.32 6.32 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.18 8.18 0 004.78 1.52V6.76a4.85 4.85 0 01-1.01-.07z"/>
            </svg>
            @neogen.store
          </a>
          <span>MADE IN KSA · صُنِع في المملكة العربية السعودية</span>
        </div>
      </div>
    </div>
  </footer>
);

// Product Card
const WORKS_WITH_MAP = {
  'GM-GPU-NVD-001': ['NZXT H9','Corsair H150i','PSU 1000W'],
  'NT-MPC-MNF-001': ['UniFi U6 Pro','Cat6a Cable','Rack 12U'],
  'SH-HUB-AQRA-001': ['Aqara Temp Sensor','Aqara Switch M2','HomeKit'],
  'NG-MKR-007':     ['PLA+ Filament','Prusa Nozzle 0.4mm','PEI Sheet'],
  'NT-WAP-TPL-002': ['Omada OC200','Cat6a Cable','PoE Injector'],
  'GM-STR-ELG-001': ['Elgato Key Light','Elgato Wave:3','USB Hub 4-Port'],
  'NG-DRN-008':     ['ND Filter Set','DJI Charging Hub','Carry Case'],
  'GM-PGA-JSA-001': ['HDMI 2.1 Cable','USB-C 100W','SSD Enclosure'],
};

const ProductCard = ({sku, ar, en, price, was, tag, brand, ph='product'}) => {
  const onSale = was && was > 0 && was !== price;
  const compatible = WORKS_WITH_MAP[sku] || [];
  return (
    <article className="card" style={{display:'flex', flexDirection:'column', position:'relative', overflow:'hidden'}}>
      {tag && (
        <div style={{position:'absolute', insetInlineStart:12, insetBlockStart:12, zIndex:2}}>
          <span className={`chip ${tag==='تخفيض'?'chip-sale':tag==='جديد'?'chip-sky':'chip-accent'}`}>{tag}</span>
        </div>
      )}
      <div className="ph" style={{aspectRatio:'4/3', borderRadius:'var(--r-2) var(--r-2) 0 0', border:'none', borderBottom:'1px solid var(--rule)'}}>
        <span className="ph-label">{ph}</span>
      </div>
      <div style={{padding:16, display:'flex', flexDirection:'column', gap:8, flex:1}}>
        <div style={{display:'flex', justifyContent:'space-between', alignItems:'center'}}>
          <span className="mono" style={{fontSize:10, color:'var(--dim)'}}>{sku}</span>
          {brand && <span className="mono-up" style={{color:'var(--ink-4)', fontSize:9}}>{brand}</span>}
        </div>
        <h3 style={{margin:0, fontSize:14, fontWeight:600, lineHeight:1.4, color:'var(--ink)'}}>{ar}</h3>
        {en && <span style={{fontFamily:'var(--f-wordmark)', fontSize:11, color:'var(--ink-4)'}}>{en}</span>}
        <div style={{display:'flex', justifyContent:'space-between', alignItems:'flex-end', marginTop:'auto', paddingTop:8}}>
          <div className="price">
            <span className="price-now">{price.toLocaleString('en-US')}</span>
            <span className="price-sar">SAR</span>
            {onSale && <span className="price-was">{was.toLocaleString('en-US')}</span>}
          </div>
          <button className="btn btn-sm" style={{padding:'7px 12px', fontSize:12, borderRadius:'var(--r-1)'}}>+</button>
        </div>
      </div>
      {compatible.length > 0 && (
        <div style={{borderTop:'1px solid var(--rule)', padding:'10px 16px', background:'var(--surface-2)', borderRadius:'0 0 var(--r-2) var(--r-2)'}}>
          <div style={{fontFamily:'var(--f-mono)', fontSize:9, textTransform:'uppercase', letterSpacing:'0.08em', color:'var(--dim)', marginBottom:6}}>يعمل مع</div>
          <div style={{display:'flex', gap:5, flexWrap:'wrap'}}>
            {compatible.map((c,i)=>(
              <span key={i} style={{fontFamily:'var(--f-mono)', fontSize:10, padding:'2px 7px', border:'1px solid var(--rule)', color:'var(--ink-4)', background:'var(--surface)', borderRadius:2}}>{c}</span>
            ))}
          </div>
        </div>
      )}
    </article>
  );
};

// Section header
const SectionHeader = ({n, eyebrow, title, subtitle, action}) => (
  <div style={{marginBottom:32}}>
    <div className="section-mark" style={{marginBottom:20}}>
      <span>{String(n).padStart(2,'0')}</span>
      <span style={{color:'var(--ink-3)'}}>· {eyebrow}</span>
    </div>
    <div style={{display:'flex', justifyContent:'space-between', alignItems:'flex-end', gap:32, flexWrap:'wrap'}}>
      <div>
        <h2 className="t-h2" style={{margin:0, textWrap:'balance', color:'var(--indigo)'}}>{title}</h2>
        {subtitle && <p className="t-body" style={{margin:'10px 0 0', maxWidth:560}}>{subtitle}</p>}
      </div>
      {action && (
        <a href="#" style={{
          color:'var(--accent-deep)', fontSize:13, fontWeight:600,
          fontFamily:'var(--f-wordmark)', display:'flex', alignItems:'center', gap:6
        }}>{action} →</a>
      )}
    </div>
  </div>
);

// Sample products
const SAMPLE_PRODUCTS = [
  {sku:'GM-GPU-NVD-001', ar:'إنفيديا RTX 5090 Founders Edition', en:'NVIDIA RTX 5090 FE 32GB', price:22999, was:26449, tag:'تخفيض', brand:'NVIDIA', ph:'GPU card'},
  {sku:'NT-MPC-MNF-001', ar:'MinisForum MS-01 خادم هوم لاب', en:'MinisForum MS-01 Homelab', price:3999, was:4599, tag:'تخفيض', brand:'MinisForum', ph:'mini-pc'},
  {sku:'SH-HUB-AQRA-001', ar:'مركز Aqara Hub M3', en:'Aqara Hub M3 · Matter', price:899, was:1039, tag:'تخفيض', brand:'Aqara', ph:'smart hub'},
  {sku:'NG-MKR-007', ar:'طابعة Prusa Mini+', en:'Prusa Mini+ 3D Printer', price:3299, was:3799, tag:'تخفيض', brand:'Prusa', ph:'3D printer'},
  {sku:'NT-WAP-TPL-002', ar:'TP-Link Omada EAP650', en:'EAP650 · WiFi 6 AX3000', price:799, was:919, tag:'جديد', brand:'TP-Link', ph:'WiFi AP'},
  {sku:'GM-STR-ELG-001', ar:'Elgato Stream Deck MK.2', en:'Stream Deck · 15 LCD keys', price:949, was:1099, tag:'جديد', brand:'Elgato', ph:'stream deck'},
  {sku:'NG-DRN-008', ar:'DJI Pocket 3 كاميرا جيب', en:'DJI Pocket 3 Gimbal Camera', price:2999, was:3449, tag:'تخفيض', brand:'DJI', ph:'gimbal cam'},
  {sku:'GM-PGA-JSA-001', ar:'JSAUX دوك 6-في-1 Steam Deck', en:'JSAUX 6-in-1 Dock', price:459, was:529, tag:'تخفيض', brand:'JSAUX', ph:'USB-C dock'},
];

Object.assign(window, {Logo, TopBar, Header, Footer, ProductCard, SectionHeader, SAMPLE_PRODUCTS});
