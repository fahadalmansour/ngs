// Terms & Conditions — NeoGen Store
const Terms = () => {
  const sections = [
    {
      n:'01', title:'تعريفات',
      body:`"نيوجن" أو "المتجر" يقصد بها نيوجن للتجارة الإلكترونية، سجل تجاري 7053130576، الرياض، المملكة العربية السعودية.
"العميل" يقصد به أي شخص يتصفح الموقع أو يُجري عملية شراء.
"المنتج" يقصد به أي سلعة أو بطاقة رقمية معروضة على المتجر.
"الطلب" يقصد به عملية الشراء المؤكدة برقم طلب.`
    },
    {
      n:'02', title:'القبول والموافقة',
      body:`باستخدامك للموقع أو إتمام عملية شراء، فأنت توافق على هذه الشروط والأحكام كاملةً. إذا لم توافق على أي بند، يرجى عدم استخدام المتجر.
نحتفظ بحق تعديل هذه الشروط في أي وقت. التغييرات تسري فور نشرها، واستمرارك في استخدام المتجر يعني قبولك للتعديلات.`
    },
    {
      n:'03', title:'الملكية الفكرية',
      body:`كل محتوى المتجر — من شعار، تصميم، نصوص، صور، وبيانات منتجات — هو ملك لنيوجن أو لأصحابه المرخصين.
لا يُسمح بنسخ أو إعادة نشر أي محتوى دون إذن خطي مسبق. يُسمح بالاقتباس المحدود للأغراض الشخصية غير التجارية مع الإشارة للمصدر.`
    },
    {
      n:'04', title:'الأسعار والدفع',
      body:`جميع الأسعار بالريال السعودي وشاملة ضريبة القيمة المضافة 15%.
نحتفظ بحق تعديل الأسعار في أي وقت. السعر المعتمد هو السعر وقت تأكيد الطلب.
الدفع يكون عبر وسائل الدفع المعتمدة على الموقع. لا تُحفظ بيانات البطاقات على خوادمنا — تُعالَج عبر بوابات دفع معتمدة.
في حالة رفض الدفع، يُلغى الطلب تلقائياً ويُبلَّغ العميل.`
    },
    {
      n:'05', title:'الطلبات والتسليم',
      body:`تأكيد الطلب يصل بالبريد الإلكتروني. الطلب ملزم بعد التأكيد.
مدة التسليم تقديرية وليست مضمونة في ظروف القوة القاهرة (إضرابات، كوارث، قرارات حكومية).
المسؤولية عن صحة عنوان الشحن تقع على العميل كاملاً.
نيوجن غير مسؤولة عن التأخير الناتج عن شركات الشحن بعد تسليم الشحنة لها.`
    },
    {
      n:'06', title:'الإرجاع والاسترداد',
      body:`تفاصيل سياسة الإرجاع مذكورة في صفحة الإرجاع والاستبدال وتُعدّ جزءاً لا يتجزأ من هذه الشروط.
البطاقات الرقمية المفعّلة أو المكشوف رمزها غير قابلة للإرجاع أو الاسترداد بأي حال.`
    },
    {
      n:'07', title:'الضمان',
      body:`منتجاتنا مشمولة بضمان المصنع الأصلي. تفاصيل الضمان مذكورة في صفحة الضمان والصيانة.
نيوجن لا تتحمل مسؤولية ما يتجاوز التزامات ضمان المصنع.
أي ضمان موسّع إضافي هو اتفاق منفصل بين العميل ونيوجن.`
    },
    {
      n:'08', title:'المسؤولية المحدودة',
      body:`نيوجن غير مسؤولة عن أي خسائر غير مباشرة أو تبعية أو أضرار خاصة ناتجة عن استخدام المنتجات.
الحد الأقصى لمسؤوليتنا في أي حالة هو قيمة المنتج المشترى.
هذا التحديد لا ينطبق في حالات الإهمال الجسيم أو الغش.`
    },
    {
      n:'09', title:'الخصوصية وحماية البيانات',
      body:`نجمع البيانات الضرورية لتنفيذ الطلبات وتحسين الخدمة. لا نبيع بياناتك لأطراف ثالثة.
بموافقتك، قد نرسل رسائل تسويقية. يمكنك إلغاء الاشتراك في أي وقت.
التفاصيل الكاملة في سياسة الخصوصية المنشورة على الموقع.`
    },
    {
      n:'10', title:'الاختصاص القضائي',
      body:`هذه الشروط تخضع لقوانين المملكة العربية السعودية.
أي نزاع يُحل أولاً بالتفاوض الودي. إذا تعذّر الحل، يُحال للجهات القضائية المختصة في مدينة الرياض.`
    },
  ];

  return (
    <div dir="rtl" style={{background:'var(--bg)', minHeight:'100%', fontFamily:'var(--f-ar)'}}>
      <TopBar/>
      <Header/>

      <section style={{borderBottom:'1px solid var(--rule)'}}>
        <div style={{maxWidth:1440, margin:'0 auto', padding:'56px 48px 40px'}}>
          <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:16}}>الرئيسية / الشروط والأحكام</div>
          <h1 className="t-h1" style={{margin:'0 0 12px'}}>الشروط والأحكام</h1>
          <div style={{display:'flex', gap:16, flexWrap:'wrap'}}>
            <span className="chip">آخر تحديث: 1 مايو 2026</span>
            <span className="chip">س.ت 7053130576</span>
            <span className="chip">المملكة العربية السعودية</span>
          </div>
        </div>
      </section>

      <div style={{maxWidth:1440, margin:'0 auto', padding:'48px 48px 96px', display:'grid', gridTemplateColumns:'220px 1fr', gap:56, alignItems:'start'}}>

        {/* TOC */}
        <aside style={{position:'sticky', top:24}}>
          <div style={{fontFamily:'var(--f-mono)', fontSize:10, textTransform:'uppercase', letterSpacing:'0.1em', color:'var(--ink-4)', marginBottom:12}}>فهرس المحتوى</div>
          <div style={{display:'flex', flexDirection:'column', gap:4}}>
            {sections.map(({n,title},i)=>(
              <a key={i} href={`#term-${n}`} style={{
                display:'flex', gap:10, alignItems:'center', padding:'8px 10px',
                borderRadius:'var(--r-1)', fontSize:13, color:'var(--ink-4)',
                textDecoration:'none', fontFamily:'var(--f-ar)'
              }}>
                <span style={{fontFamily:'var(--f-mono)', fontSize:10, color:'var(--dim)'}}>{n}</span>
                {title}
              </a>
            ))}
          </div>
        </aside>

        {/* Content */}
        <main style={{display:'flex', flexDirection:'column', gap:40}}>
          <div style={{
            background:'rgba(245,158,11,0.08)', border:'1px solid rgba(245,158,11,0.25)',
            borderRadius:'var(--r-2)', padding:'16px 20px',
            fontSize:13, color:'var(--ink-3)', lineHeight:1.65
          }}>
            ⚠️ هذه الشروط ملزمة قانونياً. يُنصح بقراءتها كاملاً قبل إجراء أي عملية شراء.
          </div>

          {sections.map(({n,title,body},i)=>(
            <div key={i} id={`term-${n}`} style={{
              background:'var(--surface)', border:'1px solid var(--rule)',
              borderRadius:'var(--r-2)', padding:32
            }}>
              <div style={{display:'flex', gap:16, alignItems:'baseline', marginBottom:16}}>
                <span style={{fontFamily:'var(--f-mono)', fontSize:11, color:'var(--accent-deep)', textTransform:'uppercase', letterSpacing:'0.1em', flexShrink:0}}>{n}</span>
                <h2 style={{margin:0, fontSize:18, fontWeight:700, color:'var(--indigo)'}}>{title}</h2>
              </div>
              <div style={{fontSize:14, lineHeight:1.85, color:'var(--ink-3)', whiteSpace:'pre-line'}}>{body}</div>
            </div>
          ))}

          <div style={{
            background:'var(--indigo)', borderRadius:'var(--r-2)', padding:32, color:'#fff'
          }}>
            <div style={{fontFamily:'var(--f-mono)', fontSize:10, textTransform:'uppercase', letterSpacing:'0.1em', color:'rgba(255,255,255,0.4)', marginBottom:8}}>للتواصل والاستفسارات القانونية</div>
            <div style={{fontWeight:700, fontSize:16, marginBottom:4}}>نيوجن للتجارة الإلكترونية</div>
            <div style={{fontSize:13, color:'rgba(255,255,255,0.65)', lineHeight:1.75}}>
              البريد: support@neogen.store<br/>
              واتساب: 0570131122<br/>
              الرياض، المملكة العربية السعودية · س.ت 7053130576
            </div>
          </div>
        </main>
      </div>
      <Footer/>
    </div>
  );
};
window.Terms = Terms;
