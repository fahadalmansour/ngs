// Services & Pricing — NeoGen Store
const Pricing = () => {
  const [billing, setBilling] = React.useState('yearly');

  const plans = [
    {
      name:'أساسي', nameEn:'Starter',
      priceM:0, priceY:0, note:'مجاني دائماً',
      cta:'ابدأ مجاناً', ctaStyle:'btn-ghost',
      features:[
        {v:true, label:'تصفح الكتالوج كاملاً'},
        {v:true, label:'قائمة مفضلة (حتى 20 منتج)'},
        {v:true, label:'تتبع الطلبات'},
        {v:true, label:'دعم عبر البريد'},
        {v:false, label:'شحن مجاني دائماً'},
        {v:false, label:'أسعار حصرية'},
        {v:false, label:'دعم واتساب أولوية'},
        {v:false, label:'ضمان موسّع'},
        {v:false, label:'مدير حساب مخصص'},
      ]
    },
    {
      name:'بلس', nameEn:'Plus',
      priceM:49, priceY:39, note:'شهرياً مع الباقة السنوية',
      cta:'اشترك الآن', ctaStyle:'',
      highlight:true,
      badge:'الأكثر طلباً',
      features:[
        {v:true, label:'تصفح الكتالوج كاملاً'},
        {v:true, label:'قائمة مفضلة غير محدودة'},
        {v:true, label:'تتبع الطلبات'},
        {v:true, label:'شحن مجاني داخل المملكة'},
        {v:true, label:'أسعار حصرية (5–10% خصم)'},
        {v:true, label:'دعم واتساب أولوية'},
        {v:false, label:'ضمان موسّع'},
        {v:false, label:'مدير حساب مخصص'},
        {v:false, label:'فاتورة B2B تلقائية'},
      ]
    },
    {
      name:'برو', nameEn:'Pro Business',
      priceM:199, priceY:159, note:'للشركات والمحترفين',
      cta:'تواصل للتفعيل', ctaStyle:'btn-dark',
      features:[
        {v:true, label:'تصفح الكتالوج كاملاً'},
        {v:true, label:'قائمة مفضلة غير محدودة'},
        {v:true, label:'تتبع الطلبات'},
        {v:true, label:'شحن مجاني لكل دول الخليج'},
        {v:true, label:'أسعار حصرية (حتى 15% خصم)'},
        {v:true, label:'دعم واتساب أولوية فورية'},
        {v:true, label:'ضمان موسّع على منتجات مختارة'},
        {v:true, label:'مدير حساب مخصص'},
        {v:true, label:'فاتورة B2B تلقائية'},
      ]
    },
  ];

  const services = [
    {icon:'🌐', title:'إعداد الشبكة المنزلية', price:'ابتداءً من 499 ريال', desc:'تصميم وتركيب شبكة Wi-Fi 6 احترافية مع إعداد UniFi أو Omada.', tag:'الأكثر طلباً'},
    {icon:'🏠', title:'حزمة البيت الذكي', price:'ابتداءً من 899 ريال', desc:'تركيب وبرمجة منظومة Aqara أو Home Assistant مع تكامل الأجهزة.', tag:'جديد'},
    {icon:'🖥️', title:'بناء هوم لاب', price:'بالتشاور', desc:'تصميم خادم Proxmox مع NAS وإعداد الشبكة والخدمات (Docker, k3s).', tag:''},
    {icon:'🔧', title:'استشارة تقنية', price:'199 ريال / ساعة', desc:'جلسة مع متخصص تقني للإجابة على أسئلتك وحل مشكلاتك التقنية.', tag:''},
    {icon:'📡', title:'إعداد VPN والأمان', price:'ابتداءً من 349 ريال', desc:'إعداد WireGuard أو OpenVPN مع firewall rules وحماية الشبكة.', tag:''},
    {icon:'🏢', title:'شبكات الأعمال', price:'بالتشاور', desc:'تصميم وتنفيذ بنية تحتية للشركات — مكاتب صغيرة وكبيرة.', tag:''},
  ];

  const price = (plan) => billing==='yearly' ? plan.priceY : plan.priceM;

  return (
    <div dir="rtl" style={{background:'var(--bg)', minHeight:'100%', fontFamily:'var(--f-ar)'}}>
      <TopBar/>
      <Header/>

      {/* Header */}
      <section style={{borderBottom:'1px solid var(--rule)'}}>
        <div style={{maxWidth:1440, margin:'0 auto', padding:'64px 48px 48px', textAlign:'center'}}>
          <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:16}}>الرئيسية / الخدمات والأسعار</div>
          <h1 className="t-h1" style={{margin:'0 0 16px'}}>الخدمات والاشتراكات</h1>
          <p className="t-body" style={{margin:'0 auto 32px', maxWidth:520}}>اشتراك بلس أو برو يمنحك شحناً مجانياً وأسعاراً حصرية وأولوية في الدعم.</p>

          {/* Billing toggle */}
          <div style={{display:'inline-flex', gap:0, border:'1px solid var(--rule)', borderRadius:'var(--r-pill)', overflow:'hidden', background:'var(--surface)'}}>
            {[['monthly','شهري'],['yearly','سنوي']].map(([val,label],i)=>(
              <button key={i} onClick={()=>setBilling(val)} style={{
                padding:'10px 24px', border:'none', cursor:'pointer',
                background: billing===val?'var(--indigo)':'transparent',
                color: billing===val?'#fff':'var(--ink-4)',
                fontFamily:'var(--f-ar)', fontSize:14, fontWeight: billing===val?600:400,
                transition:'all .2s'
              }}>
                {label}
                {val==='yearly' && <span style={{
                  marginRight:8, fontFamily:'var(--f-mono)', fontSize:10,
                  color: billing==='yearly'?'var(--accent)':'var(--good)',
                  textTransform:'uppercase'
                }}>وفّر 20%</span>}
              </button>
            ))}
          </div>
        </div>
      </section>

      {/* Plans */}
      <div style={{maxWidth:1200, margin:'0 auto', padding:'56px 48px 0'}}>
        <div style={{display:'grid', gridTemplateColumns:'repeat(3,1fr)', gap:24}}>
          {plans.map((plan,i)=>(
            <div key={i} style={{
              background: plan.highlight?'var(--indigo)':'var(--surface)',
              border:`2px solid ${plan.highlight?'var(--accent)':'var(--rule)'}`,
              borderRadius:'var(--r-3)', padding:32, position:'relative',
              boxShadow: plan.highlight?'var(--shadow-lg)':'var(--shadow-sm)',
              color: plan.highlight?'#fff':'inherit'
            }}>
              {plan.badge && (
                <div style={{position:'absolute', top:-14, insetInlineStart:'50%', transform:'translateX(50%)'}}>
                  <span className="chip chip-accent" style={{whiteSpace:'nowrap'}}>{plan.badge}</span>
                </div>
              )}
              <div style={{fontFamily:'var(--f-mono)', fontSize:10, textTransform:'uppercase', letterSpacing:'0.1em', color: plan.highlight?'rgba(255,255,255,0.45)':'var(--dim)', marginBottom:8}}>{plan.nameEn}</div>
              <div style={{fontWeight:700, fontSize:22, color: plan.highlight?'#fff':'var(--indigo)', marginBottom:4}}>{plan.name}</div>
              <div style={{margin:'20px 0', paddingBottom:20, borderBottom:`1px solid ${plan.highlight?'rgba(255,255,255,0.1)':'var(--rule)'}`}}>
                {plan.priceM === 0 ? (
                  <div style={{fontFamily:'var(--f-wordmark)', fontWeight:700, fontSize:36, color: plan.highlight?'#fff':'var(--indigo)'}}>مجاني</div>
                ) : (
                  <div style={{display:'flex', alignItems:'baseline', gap:6}}>
                    <span style={{fontFamily:'var(--f-wordmark)', fontWeight:700, fontSize:40, color: plan.highlight?'#fff':'var(--indigo)'}}>{price(plan)}</span>
                    <span style={{fontFamily:'var(--f-mono)', fontSize:13, color: plan.highlight?'rgba(255,255,255,0.5)':'var(--ink-4)'}}>ريال / شهر</span>
                  </div>
                )}
                <div style={{fontFamily:'var(--f-mono)', fontSize:10, color: plan.highlight?'rgba(255,255,255,0.4)':'var(--dim)', marginTop:4, textTransform:'uppercase', letterSpacing:'0.06em'}}>{plan.note}</div>
              </div>

              <div style={{display:'flex', flexDirection:'column', gap:10, marginBottom:28}}>
                {plan.features.map(({v,label},fi)=>(
                  <div key={fi} style={{display:'flex', gap:10, alignItems:'flex-start', fontSize:13, color: v?(plan.highlight?'rgba(255,255,255,0.85)':'var(--ink-3)'):(plan.highlight?'rgba(255,255,255,0.2)':'var(--dim)')}}>
                    <span style={{flexShrink:0, fontWeight:700, color: v?'var(--good)':(plan.highlight?'rgba(255,255,255,0.15)':'var(--rule-strong)')}}>{v?'✓':'✕'}</span>
                    {label}
                  </div>
                ))}
              </div>

              <button className={`btn ${plan.ctaStyle}`} style={{
                width:'100%', justifyContent:'center', borderRadius:'var(--r-2)', padding:14,
                ...(plan.highlight?{background:'var(--accent)', color:'var(--indigo-deep)'}:{})
              }}>{plan.cta}</button>
            </div>
          ))}
        </div>
      </div>

      {/* Services */}
      <div style={{maxWidth:1440, margin:'0 auto', padding:'72px 48px 96px'}}>
        <SectionHeader n={2} eyebrow="Professional Services" title="خدمات مدفوعة" subtitle="تركيب وإعداد على يد متخصصين"/>
        <div style={{display:'grid', gridTemplateColumns:'repeat(3,1fr)', gap:20, marginTop:40}}>
          {services.map(({icon,title,price:p,desc,tag},i)=>(
            <div key={i} style={{
              background:'var(--surface)', border:'1px solid var(--rule)',
              borderRadius:'var(--r-2)', padding:28, boxShadow:'var(--shadow-sm)',
              display:'flex', flexDirection:'column', gap:12
            }}>
              <div style={{display:'flex', justifyContent:'space-between', alignItems:'flex-start'}}>
                <span style={{fontSize:28}}>{icon}</span>
                {tag && <span className={`chip ${tag==='الأكثر طلباً'?'chip-accent':'chip-sky'}`} style={{fontSize:10}}>{tag}</span>}
              </div>
              <div style={{fontWeight:700, fontSize:16, color:'var(--indigo)'}}>{title}</div>
              <div style={{fontFamily:'var(--f-wordmark)', fontWeight:700, fontSize:16, color:'var(--accent-deep)'}}>{p}</div>
              <div style={{fontSize:13, lineHeight:1.65, color:'var(--ink-4)', flex:1}}>{desc}</div>
              <button className="btn btn-ghost btn-sm" style={{borderRadius:'var(--r-1)', width:'100%', justifyContent:'center', marginTop:4}}>
                استفسر الآن
              </button>
            </div>
          ))}
        </div>
      </div>
      <Footer/>
    </div>
  );
};
window.Pricing = Pricing;
