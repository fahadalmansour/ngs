// Contact — NeoGen Store
const Contact = () => {
  const [form, setForm] = React.useState({name:'', email:'', phone:'', subject:'', msg:''});
  const set = k => e => setForm(f=>({...f,[k]:e.target.value}));

  const inputStyle = {
    width:'100%', padding:'12px 16px',
    border:'1px solid var(--rule-strong)', borderRadius:'var(--r-2)',
    background:'var(--bg)', fontFamily:'var(--f-ar)', fontSize:14,
    color:'var(--ink)', outline:'none', boxSizing:'border-box', lineHeight:1.5
  };

  return (
    <div dir="rtl" style={{background:'var(--bg)', minHeight:'100%', fontFamily:'var(--f-ar)'}}>
      <TopBar/>
      <Header/>

      {/* Page header */}
      <section style={{borderBottom:'1px solid var(--rule)'}}>
        <div style={{maxWidth:1440, margin:'0 auto', padding:'56px 48px 40px'}}>
          <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:16}}>الرئيسية / تواصل</div>
          <h1 className="t-h1" style={{margin:'0 0 12px'}}>تواصل معنا</h1>
          <p className="t-body" style={{margin:0, maxWidth:520}}>فريقنا متاح من الساعة 9 صباحاً حتى 9 مساءً، سبعة أيام في الأسبوع.</p>
        </div>
      </section>

      <div style={{maxWidth:1440, margin:'0 auto', padding:'48px 48px 96px', display:'grid', gridTemplateColumns:'1fr 420px', gap:40, alignItems:'start'}}>

        {/* Form */}
        <div style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', padding:40, boxShadow:'var(--shadow-sm)'}}>
          <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:28}}>01 · أرسل رسالة</div>
          <div style={{display:'grid', gridTemplateColumns:'1fr 1fr', gap:16, marginBottom:16}}>
            <div>
              <label style={{display:'block', fontSize:13, fontWeight:600, color:'var(--ink-3)', marginBottom:6}}>الاسم الكامل</label>
              <input value={form.name} onChange={set('name')} placeholder="محمد العمري" style={inputStyle}/>
            </div>
            <div>
              <label style={{display:'block', fontSize:13, fontWeight:600, color:'var(--ink-3)', marginBottom:6}}>البريد الإلكتروني</label>
              <input value={form.email} onChange={set('email')} placeholder="you@example.com" dir="ltr" style={inputStyle}/>
            </div>
          </div>
          <div style={{display:'grid', gridTemplateColumns:'1fr 1fr', gap:16, marginBottom:16}}>
            <div>
              <label style={{display:'block', fontSize:13, fontWeight:600, color:'var(--ink-3)', marginBottom:6}}>رقم الجوال</label>
              <input value={form.phone} onChange={set('phone')} placeholder="05XXXXXXXX" dir="ltr" style={inputStyle}/>
            </div>
            <div>
              <label style={{display:'block', fontSize:13, fontWeight:600, color:'var(--ink-3)', marginBottom:6}}>الموضوع</label>
              <select value={form.subject} onChange={set('subject')} style={inputStyle}>
                <option value="">اختر موضوعاً</option>
                <option>سؤال عن منتج</option>
                <option>متابعة طلب</option>
                <option>طلب إرجاع</option>
                <option>مطالبة ضمان</option>
                <option>طلب فاتورة ضريبية</option>
                <option>استفسار شحن</option>
                <option>أخرى</option>
              </select>
            </div>
          </div>
          <div style={{marginBottom:24}}>
            <label style={{display:'block', fontSize:13, fontWeight:600, color:'var(--ink-3)', marginBottom:6}}>رسالتك</label>
            <textarea value={form.msg} onChange={set('msg')} rows={5} placeholder="اكتب تفاصيل استفسارك هنا..." style={{...inputStyle, resize:'vertical'}}/>
          </div>
          <button className="btn" style={{borderRadius:'var(--r-2)', width:'100%', justifyContent:'center', padding:'14px'}}>
            إرسال الرسالة →
          </button>
          <div style={{marginTop:14, textAlign:'center', fontFamily:'var(--f-mono)', fontSize:11, color:'var(--dim)', textTransform:'uppercase', letterSpacing:'0.08em'}}>
            نرد خلال أقل من ساعة عمل
          </div>
        </div>

        {/* Contact channels */}
        <div style={{display:'flex', flexDirection:'column', gap:16}}>
          <div style={{fontFamily:'var(--f-mono)', fontSize:10, textTransform:'uppercase', letterSpacing:'0.1em', color:'var(--ink-4)', marginBottom:4}}>02 · قنوات التواصل</div>

          {[
            {icon:'💬', title:'واتساب', value:'0570131122', sub:'الأسرع — رد فوري', href:'https://wa.me/9660570131122', cta:'فتح واتساب', accent:true},
            {icon:'📧', title:'البريد الإلكتروني', value:'support@neogen.store', sub:'للاستفسارات التفصيلية', href:'mailto:support@neogen.store', cta:'إرسال بريد'},
            {icon:'📍', title:'العنوان', value:'الرياض، المملكة العربية السعودية', sub:'لا استقبال في المقر — البيع إلكتروني فقط', cta:null},
            {icon:'⏰', title:'ساعات العمل', value:'9:00 ص – 9:00 م', sub:'7 أيام في الأسبوع', cta:null},
          ].map(({icon,title,value,sub,href,cta,accent},i)=>(
            <div key={i} style={{
              background:'var(--surface)', border:`1px solid ${accent?'var(--accent)':'var(--rule)'}`,
              borderRadius:'var(--r-2)', padding:20, boxShadow:'var(--shadow-sm)',
              background: accent?'var(--accent-wash)':'var(--surface)'
            }}>
              <div style={{display:'flex', gap:14, alignItems:'flex-start'}}>
                <span style={{fontSize:22}}>{icon}</span>
                <div style={{flex:1}}>
                  <div style={{fontFamily:'var(--f-mono)', fontSize:10, textTransform:'uppercase', letterSpacing:'0.08em', color:'var(--dim)', marginBottom:4}}>{title}</div>
                  <div style={{fontWeight:600, fontSize:14, color:'var(--indigo)', marginBottom:2}}>{value}</div>
                  <div style={{fontSize:12, color:'var(--ink-4)'}}>{sub}</div>
                </div>
                {cta && href && (
                  <a href={href} style={{
                    display:'inline-flex', alignItems:'center', padding:'8px 14px',
                    background: accent?'var(--accent)':'var(--indigo)', color: accent?'var(--indigo-deep)':'#fff',
                    borderRadius:'var(--r-1)', fontSize:12, fontWeight:600, whiteSpace:'nowrap'
                  }}>{cta}</a>
                )}
              </div>
            </div>
          ))}

          {/* Available */}
          <div style={{
            background:'var(--surface)', border:'1px solid var(--rule)',
            borderRadius:'var(--r-2)', padding:20
          }}>
            <div style={{fontFamily:'var(--f-mono)', fontSize:10, textTransform:'uppercase', letterSpacing:'0.08em', color:'var(--dim)', marginBottom:12}}>نغطي دول الخليج</div>
            <div style={{display:'flex', gap:12, flexWrap:'wrap'}}>
              {[['🇸🇦','السعودية'],['🇦🇪','الإمارات'],['🇰🇼','الكويت'],['🇧🇭','البحرين'],['🇴🇲','عُمان'],['🇶🇦','قطر']].map(([f,c],i)=>(
                <div key={i} style={{display:'flex', flexDirection:'column', alignItems:'center', gap:4}}>
                  <span style={{fontSize:22}}>{f}</span>
                  <span style={{fontSize:11, color:'var(--ink-4)'}}>{c}</span>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>
      <Footer/>
    </div>
  );
};
window.Contact = Contact;
