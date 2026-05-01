// About — NeoGen Store
const About = () => (
  <div dir="rtl" style={{background:'var(--bg)', minHeight:'100%', fontFamily:'var(--f-ar)'}}>
    <TopBar/>
    <Header/>

    {/* Hero */}
    <section style={{
      borderBottom:'1px solid var(--rule)',
      background:`radial-gradient(900px circle at 60% 40%, rgba(56,189,248,0.07), transparent 55%),
        linear-gradient(rgba(10,10,10,0.035) 1px, transparent 1px),
        linear-gradient(90deg, rgba(10,10,10,0.035) 1px, transparent 1px), #F8FAFC`,
      backgroundSize:'auto, 48px 48px, 48px 48px, auto'
    }}>
      <div style={{maxWidth:1440, margin:'0 auto', padding:'80px 48px 72px'}}>
        <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:24}}>الرئيسية / من نحن</div>
        <div style={{display:'grid', gridTemplateColumns:'1fr 1fr', gap:80, alignItems:'center'}}>
          <div>
            <div style={{
              display:'inline-flex', alignItems:'center', gap:8, marginBottom:24,
              padding:'6px 14px', border:'1px solid var(--rule-strong)', borderRadius:'var(--r-pill)',
              fontFamily:'var(--f-mono)', fontSize:11, textTransform:'uppercase', letterSpacing:'0.08em', color:'var(--ink-4)'
            }}>
              <span className="dot dot-on"></span>
              متجر سعودي معتمد · س.ت 7053130576
            </div>
            <h1 style={{
              fontFamily:'var(--f-wordmark)', fontWeight:700,
              fontSize:'clamp(44px,5.5vw,80px)', lineHeight:0.95,
              letterSpacing:'-0.02em', margin:'0 0 24px', color:'var(--indigo)'
            }}>
              تقنية<br/>
              <span style={{fontStyle:'italic', color:'var(--accent)', fontWeight:400}}>بلا تعقيد.</span>
            </h1>
            <p style={{fontSize:16, lineHeight:1.75, color:'var(--ink-3)', maxWidth:520, margin:0}}>
              نيوجن ستور متجر تقني سعودي أسسناه لمحبي الشبكات، هوم لاب، البيوت الذكية، والألعاب.
              نختار كل منتج بعناية، نختبره بأنفسنا، ونشحنه لكل دول الخليج.
            </p>
          </div>
          <div style={{display:'grid', gridTemplateColumns:'1fr 1fr', gap:16}}>
            {[
              {n:'215+', label:'منتج مختار', sub:'عبر 6 فئات'},
              {n:'6', label:'دول خليجية', sub:'KSA · UAE · KW · BH · OM · QA'},
              {n:'2026', label:'سنة التأسيس', sub:'الرياض، المملكة العربية السعودية'},
              {n:'<1h', label:'وقت الرد', sub:'دعم 9ص–9م يومياً'},
            ].map(({n,label,sub},i)=>(
              <div key={i} style={{
                background:'var(--surface)', border:'1px solid var(--rule)',
                borderRadius:'var(--r-2)', padding:'24px 20px', boxShadow:'var(--shadow-sm)'
              }}>
                <div style={{fontFamily:'var(--f-wordmark)', fontSize:32, fontWeight:700, color:'var(--indigo)', lineHeight:1}}>{n}</div>
                <div style={{fontWeight:600, fontSize:14, color:'var(--ink-2)', marginTop:8}}>{label}</div>
                <div style={{fontFamily:'var(--f-mono)', fontSize:10, color:'var(--dim)', marginTop:4, textTransform:'uppercase', letterSpacing:'0.06em'}}>{sub}</div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </section>

    {/* Story */}
    <section style={{borderBottom:'1px solid var(--rule)'}}>
      <div style={{maxWidth:1440, margin:'0 auto', padding:'72px 48px'}}>
        <SectionHeader n={1} eyebrow="Our Story" title="لماذا نيوجن؟" subtitle="قصتنا ببساطة"/>
        <div style={{display:'grid', gridTemplateColumns:'1fr 1fr', gap:64, marginTop:40}}>
          <div style={{fontSize:15, lineHeight:1.85, color:'var(--ink-3)', display:'flex', flexDirection:'column', gap:20}}>
            <p style={{margin:0}}>
              بدأت نيوجن من شغف حقيقي بالتقنية. كنا نبحث عن منتجات شبكات وهوم لاب في السوق السعودي فلم نجد
              من يجمعها بشكل متخصص مع دعم فني حقيقي. قررنا أن نكون الحل.
            </p>
            <p style={{margin:0}}>
              كل منتج نبيعه مررنا به شخصياً — إما جربناه في مختبرنا أو تحققنا من مواصفاته بدقة.
              لا نضيف منتجاً للكتالوج ما لم نكن مقتنعين به تماماً.
            </p>
            <p style={{margin:0}}>
              اليوم نخدم محترفي التقنية والهواة معاً في 6 دول خليجية، مع شحن سريع، ضمان حقيقي، ودعم فني تفاعلي.
            </p>
          </div>
          <div style={{display:'flex', flexDirection:'column', gap:16}}>
            {[
              {icon:'🔬', title:'نختبر قبل أن نبيع', body:'كل منتج يمر باختبار داخلي أو مراجعة تقنية دقيقة قبل إضافته للكتالوج.'},
              {icon:'🚚', title:'شحن خليجي سريع', body:'مستودع في الرياض يضمن وصول طلبك في 2–5 أيام عمل لكل دول الخليج.'},
              {icon:'🛡️', title:'ضمان حقيقي', body:'ضمان المصنع + دعم محلي. إذا واجهت مشكلة، نحن هنا لحلّها.'},
              {icon:'💬', title:'دعم بشري', body:'فريق تقني حقيقي يرد على استفساراتك بفهم، لا ردود آلية.'},
            ].map(({icon,title,body},i)=>(
              <div key={i} style={{
                display:'flex', gap:16, padding:20,
                background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)'
              }}>
                <span style={{fontSize:24, flexShrink:0}}>{icon}</span>
                <div>
                  <div style={{fontWeight:700, fontSize:14, color:'var(--indigo)', marginBottom:4}}>{title}</div>
                  <div style={{fontSize:13, lineHeight:1.65, color:'var(--ink-4)'}}>{body}</div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </section>

    {/* Team / Values */}
    <section style={{borderBottom:'1px solid var(--rule)', background:'var(--surface-2)'}}>
      <div style={{maxWidth:1440, margin:'0 auto', padding:'72px 48px'}}>
        <SectionHeader n={2} eyebrow="What We Stand For" title="قيمنا" subtitle="ما يميّزنا"/>
        <div style={{display:'grid', gridTemplateColumns:'repeat(3,1fr)', gap:24, marginTop:40}}>
          {[
            {n:'01', title:'الصدق أولاً', body:'نذكر المواصفات الحقيقية، ونعترف بالقيود. لا مبالغة في الوصف.'},
            {n:'02', title:'التخصص التقني', body:'نفهم ما نبيعه. يمكنك سؤالنا عن أي تفصيلة تقنية في أي منتج.'},
            {n:'03', title:'خدمة ما بعد البيع', body:'علاقتنا معك لا تنتهي بانتهاء الفاتورة. الدعم والضمان جزء من الصفقة.'},
            {n:'04', title:'الاختيار المقصود', body:'215 منتج مختار بعناية أفضل من 2000 منتج عشوائي. الجودة قبل الكمية.'},
            {n:'05', title:'الشفافية الكاملة', body:'الأسعار واضحة، الضريبة محسوبة، رسوم الشحن معلنة مسبقاً.'},
            {n:'06', title:'جذور سعودية', body:'متجر سعودي بفريق سعودي يخدم مجتمع التقنية في المملكة والخليج.'},
          ].map(({n,title,body},i)=>(
            <div key={i} style={{
              background:'var(--surface)', border:'1px solid var(--rule)',
              borderRadius:'var(--r-2)', padding:'28px 24px', boxShadow:'var(--shadow-sm)'
            }}>
              <div style={{fontFamily:'var(--f-mono)', fontSize:11, color:'var(--accent-deep)', marginBottom:12, textTransform:'uppercase', letterSpacing:'0.1em'}}>{n}</div>
              <div style={{fontWeight:700, fontSize:16, color:'var(--indigo)', marginBottom:10}}>{title}</div>
              <div style={{fontSize:13, lineHeight:1.65, color:'var(--ink-4)'}}>{body}</div>
            </div>
          ))}
        </div>
      </div>
    </section>

    {/* Legal + Contact strip */}
    <section>
      <div style={{maxWidth:1440, margin:'0 auto', padding:'56px 48px'}}>
        <div style={{
          display:'grid', gridTemplateColumns:'1fr auto', alignItems:'center', gap:40,
          background:'var(--indigo)', borderRadius:'var(--r-3)', padding:'40px 48px',
          color:'#fff'
        }}>
          <div>
            <div style={{fontFamily:'var(--f-mono)', fontSize:10, textTransform:'uppercase', letterSpacing:'0.1em', color:'rgba(255,255,255,0.4)', marginBottom:12}}>بيانات رسمية</div>
            <div style={{fontWeight:700, fontSize:20, marginBottom:8}}>نيوجن للتجارة الإلكترونية</div>
            <div style={{display:'flex', gap:24, flexWrap:'wrap', fontSize:13, color:'rgba(255,255,255,0.65)'}}>
              <span>س.ت 7053130576</span>
              <span style={{opacity:0.4}}>·</span>
              <span>ضريبة القيمة المضافة 15%</span>
              <span style={{opacity:0.4}}>·</span>
              <span>الرياض، المملكة العربية السعودية</span>
              <span style={{opacity:0.4}}>·</span>
              <span>support@neogen.store</span>
            </div>
          </div>
          <div style={{display:'flex', gap:12}}>
            <button className="btn" style={{borderRadius:'var(--r-2)'}}>تواصل معنا</button>
            <button className="btn btn-ghost" style={{borderRadius:'var(--r-2)', color:'#fff', borderColor:'rgba(255,255,255,0.3)'}}>المتجر</button>
          </div>
        </div>
      </div>
    </section>

    <Footer/>
  </div>
);
window.About = About;
