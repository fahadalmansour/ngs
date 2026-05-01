// Shipping — NeoGen Store
const Shipping = () => (
  <div dir="rtl" style={{background:'var(--bg)', minHeight:'100%', fontFamily:'var(--f-ar)'}}>
    <TopBar/>
    <Header/>

    <section style={{borderBottom:'1px solid var(--rule)'}}>
      <div style={{maxWidth:1440, margin:'0 auto', padding:'56px 48px 40px'}}>
        <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:16}}>الرئيسية / الشحن والتسليم</div>
        <h1 className="t-h1" style={{margin:'0 0 12px'}}>الشحن والتسليم</h1>
        <p className="t-body" style={{margin:0, maxWidth:520}}>نشحن لكل دول الخليج من مستودعنا في الرياض.</p>
      </div>
    </section>

    <div style={{maxWidth:1440, margin:'0 auto', padding:'48px 48px 96px', display:'flex', flexDirection:'column', gap:56}}>

      {/* Delivery times */}
      <div>
        <SectionHeader n={1} eyebrow="Delivery Times" title="مدد التوصيل" subtitle="من تاريخ تأكيد الطلب"/>
        <div style={{display:'grid', gridTemplateColumns:'repeat(3,1fr)', gap:16, marginTop:32}}>
          {[
            {flag:'🇸🇦', country:'المملكة العربية السعودية', time:'2–5 أيام عمل', price:'مجاني فوق 500 ريال / 35 ريال أقل', highlight:true},
            {flag:'🇦🇪', country:'الإمارات العربية المتحدة', time:'4–7 أيام عمل', price:'يحسب عند الدفع'},
            {flag:'🇰🇼', country:'الكويت', time:'4–7 أيام عمل', price:'يحسب عند الدفع'},
            {flag:'🇧🇭', country:'البحرين', time:'4–7 أيام عمل', price:'يحسب عند الدفع'},
            {flag:'🇴🇲', country:'سلطنة عُمان', time:'5–8 أيام عمل', price:'يحسب عند الدفع'},
            {flag:'🇶🇦', country:'قطر', time:'4–7 أيام عمل', price:'يحسب عند الدفع'},
          ].map(({flag,country,time,price,highlight},i)=>(
            <div key={i} style={{
              background:'var(--surface)', border:`1px solid ${highlight?'var(--accent)':'var(--rule)'}`,
              borderRadius:'var(--r-2)', padding:24, boxShadow:'var(--shadow-sm)',
              background: highlight?'var(--accent-wash)':'var(--surface)'
            }}>
              <div style={{fontSize:32, marginBottom:12}}>{flag}</div>
              <div style={{fontWeight:700, fontSize:15, color:'var(--indigo)', marginBottom:6}}>{country}</div>
              <div style={{fontFamily:'var(--f-mono)', fontSize:13, color:'var(--accent-deep)', marginBottom:8}}>{time}</div>
              <div style={{fontSize:12, color:'var(--ink-4)'}}>{price}</div>
            </div>
          ))}
        </div>
      </div>

      {/* Carriers */}
      <div>
        <SectionHeader n={2} eyebrow="Shipping Partners" title="شركاء الشحن"/>
        <div style={{display:'grid', gridTemplateColumns:'repeat(3,1fr)', gap:16, marginTop:32}}>
          {[
            {name:'أرامكس · Aramex', note:'الشحن الداخلي والخليجي', status:'الأسرع للرياض وجدة'},
            {name:'DHL Express', note:'الشحن الدولي والخليجي', status:'مثالي للأجهزة الثقيلة'},
            {name:'سمسا · Smsa', note:'الشحن الداخلي', status:'تغطية واسعة للمملكة'},
          ].map(({name,note,status},i)=>(
            <div key={i} style={{
              background:'var(--surface)', border:'1px solid var(--rule)',
              borderRadius:'var(--r-2)', padding:24
            }}>
              <div style={{fontWeight:700, fontSize:16, color:'var(--indigo)', marginBottom:6}}>{name}</div>
              <div style={{fontSize:13, color:'var(--ink-4)', marginBottom:8}}>{note}</div>
              <div style={{fontFamily:'var(--f-mono)', fontSize:11, color:'var(--good)', textTransform:'uppercase', letterSpacing:'0.06em'}}>● {status}</div>
            </div>
          ))}
        </div>
      </div>

      {/* Notes */}
      <div style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', padding:36}}>
        <SectionHeader n={3} eyebrow="Important Notes" title="ملاحظات مهمة"/>
        <div style={{display:'grid', gridTemplateColumns:'1fr 1fr', gap:24, marginTop:24}}>
          {[
            {icon:'📦', title:'التغليف', body:'نغلّف كل منتج بعناية لضمان وصوله سليماً. المنتجات الكبيرة أو الحساسة تُشحن بتغليف مزدوج.'},
            {icon:'🔢', title:'رقم التتبع', body:'ترسل رسالة التتبع على البريد الإلكتروني وواتساب فور تجهيز الشحنة. التتبع اللحظي متاح على موقع شركة الشحن.'},
            {icon:'🏠', title:'التوصيل للباب', body:'التوصيل يكون لعنوان الباب مباشرة. تأكد من صحة العنوان عند الطلب — تعديل العنوان بعد الشحن قد يتأخر.'},
            {icon:'⚠️', title:'الجمارك الخليجية', body:'رسوم الجمارك لدول الخليج (إن وجدت) يتحملها العميل. معظم المنتجات الإلكترونية الاستهلاكية معفاة أو برسوم منخفضة.'},
            {icon:'📅', title:'أوقات المعالجة', body:'الطلبات المُسجَّلة قبل 2 ظهراً تُعالج في نفس اليوم. الطلبات بعد 2 ظهراً تُعالج في اليوم التالي.'},
            {icon:'🎄', title:'مواسم الذروة', body:'في المواسم كرمضان والأعياد، قد تزيد مدة التوصيل يومين إضافيين. سيُشار لذلك عند إتمام الطلب.'},
          ].map(({icon,title,body},i)=>(
            <div key={i} style={{display:'flex', gap:14}}>
              <span style={{fontSize:22, flexShrink:0}}>{icon}</span>
              <div>
                <div style={{fontWeight:600, fontSize:14, color:'var(--indigo)', marginBottom:4}}>{title}</div>
                <div style={{fontSize:13, lineHeight:1.65, color:'var(--ink-4)'}}>{body}</div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
    <Footer/>
  </div>
);
window.Shipping = Shipping;
