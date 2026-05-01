// FAQ — NeoGen Store
const FAQ = () => {
  const [open, setOpen] = React.useState(null);
  const toggle = i => setOpen(o => o===i ? null : i);

  const sections = [
    {
      cat:'الطلبات والشحن', icon:'📦',
      items:[
        {q:'كم يستغرق وصول الطلب؟', a:'الشحن داخل المملكة العربية السعودية يستغرق 2–5 أيام عمل. لدول الخليج الأخرى 4–8 أيام عمل. سيتم إرسال رقم التتبع فور شحن الطلب.'},
        {q:'هل الشحن مجاني؟', a:'الشحن مجاني للطلبات التي تتجاوز 500 ريال داخل المملكة. للطلبات الأقل من 500 ريال، رسوم الشحن 35 ريال. لدول الخليج الأخرى، الرسوم تُحسب حسب الوزن والوجهة عند الدفع.'},
        {q:'ما هي شركات الشحن التي تستخدمونها؟', a:'نستخدم أرامكس وDHL وسمسا داخل المملكة. لدول الخليج نعتمد على DHL و Aramex بحسب الوجهة.'},
        {q:'هل يمكن استلام الطلب من مستودعكم؟', a:'حالياً لا، المتجر إلكتروني بالكامل ولا يوجد خيار استلام. نعمل على توسيع خيارات التوصيل مستقبلاً.'},
        {q:'ماذا أفعل إذا تأخر طلبي؟', a:'إذا تجاوزت المدة المتوقعة، تواصل معنا عبر واتساب 0570131122 أو support@neogen.store مع رقم طلبك وسنتابع فوراً.'},
      ]
    },
    {
      cat:'الإرجاع والاستبدال', icon:'🔄',
      items:[
        {q:'ما هي سياسة الإرجاع؟', a:'نقبل الإرجاع خلال 14 يوماً من استلام الطلب، بشرط أن يكون المنتج في حالته الأصلية مع كامل الملحقات والتغليف. البطاقات الرقمية المفعّلة غير قابلة للإرجاع.'},
        {q:'كيف أبدأ طلب إرجاع؟', a:'تواصل معنا عبر صفحة الدعم في حسابك أو عبر واتساب مع رقم الطلب وسبب الإرجاع. سنرسل لك تعليمات الشحن خلال ساعة عمل.'},
        {q:'من يتحمل رسوم شحن الإرجاع؟', a:'إذا كان الإرجاع بسبب خطأ منّا أو عيب في المنتج، نتحمل نحن رسوم الشحن. إذا كان بسبب تغيير رأي، يتحمل العميل رسوم الإرجاع (35 ريال داخل المملكة).'},
        {q:'متى يتم استرداد المبلغ؟', a:'بعد استلام المنتج والتحقق منه، يُعالج الاسترداد خلال 3–7 أيام عمل. المبلغ يعود لنفس وسيلة الدفع الأصلية.'},
      ]
    },
    {
      cat:'الضمان والدعم', icon:'🛡️',
      items:[
        {q:'ما مدة الضمان على المنتجات؟', a:'كل منتج يأتي بضمان المصنع الأصلي (عادةً سنة واحدة أو أكثر حسب العلامة التجارية). نوفر دعماً محلياً لتسهيل عملية المطالبة.'},
        {q:'كيف أطالب بالضمان؟', a:'تواصل معنا عبر واتساب أو صفحة الدعم مع وصف المشكلة وصور توضيحية. فريقنا التقني سيقيّم الحالة ويوجهك للخطوات التالية.'},
        {q:'هل تغطي الضمان أضرار الحوادث؟', a:'الضمان يغطي عيوب الصناعة والأعطال التشغيلية فقط. الأضرار الناتجة عن سوء الاستخدام أو الحوادث غير مشمولة في الضمان الاعتيادي.'},
        {q:'هل يمكنني التواصل للحصول على نصيحة تقنية قبل الشراء؟', a:'بالتأكيد! هذا ما نتميز به. تواصل معنا عبر واتساب وسيرد عليك متخصص تقني يساعدك في اختيار المنتج المناسب لاحتياجاتك.'},
      ]
    },
    {
      cat:'الدفع والأسعار', icon:'💳',
      items:[
        {q:'ما وسائل الدفع المقبولة؟', a:'مدى، فيزا، ماستركارد، Apple Pay، Samsung Pay، التحويل البنكي، والدفع عند الاستلام (داخل الرياض فقط).'},
        {q:'هل الأسعار شاملة ضريبة القيمة المضافة؟', a:'نعم، كل الأسعار المعروضة شاملة ضريبة القيمة المضافة 15%. يمكنك طلب فاتورة ضريبية رسمية من خلال حسابك بعد إتمام الشراء.'},
        {q:'هل يمكن الدفع بالتقسيط؟', a:'نعم، خيار التقسيط متاح عبر تمارا وتابي للطلبات المؤهلة. الشروط والأقساط تظهر عند الدفع.'},
        {q:'هل أسعاركم تنافسية؟', a:'نراجع أسعارنا باستمرار. إذا وجدت نفس المنتج (أصلي، بضمان) بسعر أقل لدى متجر موثوق آخر داخل الخليج، تواصل معنا وسنحاول مطابقة السعر.'},
      ]
    },
    {
      cat:'المنتجات والتوفر', icon:'🔍',
      items:[
        {q:'هل منتجاتكم أصلية؟', a:'نعم بالكامل. نستورد مباشرة من الموزعين الرسميين والعلامات التجارية. لا نبيع منتجات مقلدة أو مستعملة.'},
        {q:'كيف أعرف إذا كان المنتج متوفراً؟', a:'كل منتج يعرض حالة التوفر (متوفر / آخر القطع / نفذت الكمية). إذا نفد منتج تريده، يمكنك تسجيل بريدك للإشعار عند توفره.'},
        {q:'هل يمكنني طلب منتج غير موجود في الكتالوج؟', a:'في حالات معينة، نعم. تواصل معنا بتفاصيل المنتج وسنحاول توفيره. الحد الأدنى للطلبات الخاصة عادةً قطعة واحدة لكن قد يستغرق التوفر أسبوعين.'},
        {q:'لماذا بعض المنتجات التقنية غير متوفرة في السوق السعودي؟', a:'بعض المنتجات تخضع لقيود الاستيراد أو لا يوجد لها توزيع رسمي في المملكة. نعمل على توسيع كتالوجنا باستمرار.'},
      ]
    },
  ];

  let globalIdx = 0;

  return (
    <div dir="rtl" style={{background:'var(--bg)', minHeight:'100%', fontFamily:'var(--f-ar)'}}>
      <TopBar/>
      <Header/>

      <section style={{borderBottom:'1px solid var(--rule)'}}>
        <div style={{maxWidth:1440, margin:'0 auto', padding:'56px 48px 40px'}}>
          <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:16}}>الرئيسية / الأسئلة الشائعة</div>
          <h1 className="t-h1" style={{margin:'0 0 12px'}}>الأسئلة الشائعة</h1>
          <p className="t-body" style={{margin:0, maxWidth:520}}>إجابات على أكثر الأسئلة تكراراً. لم تجد جوابك؟ تواصل مع فريقنا مباشرة.</p>
        </div>
      </section>

      <div style={{maxWidth:1440, margin:'0 auto', padding:'48px 48px 96px', display:'grid', gridTemplateColumns:'240px 1fr', gap:48, alignItems:'start'}}>

        {/* Category nav */}
        <aside style={{position:'sticky', top:24}}>
          <div style={{fontFamily:'var(--f-mono)', fontSize:10, textTransform:'uppercase', letterSpacing:'0.1em', color:'var(--ink-4)', marginBottom:12}}>الأقسام</div>
          <div style={{display:'flex', flexDirection:'column', gap:4}}>
            {sections.map(({cat,icon},i)=>(
              <a key={i} href={`#section-${i}`} style={{
                display:'flex', gap:10, alignItems:'center', padding:'10px 12px',
                borderRadius:'var(--r-1)', fontSize:13, color:'var(--ink-3)',
                border:'1px solid transparent', textDecoration:'none',
                transition:'all .15s'
              }}>
                <span style={{fontSize:16}}>{icon}</span>{cat}
              </a>
            ))}
          </div>
          <div style={{marginTop:32, background:'var(--indigo)', borderRadius:'var(--r-2)', padding:20}}>
            <div style={{fontFamily:'var(--f-mono)', fontSize:10, textTransform:'uppercase', letterSpacing:'0.08em', color:'rgba(255,255,255,0.4)', marginBottom:8}}>لم تجد إجابة؟</div>
            <div style={{fontWeight:600, fontSize:13, color:'#fff', marginBottom:12}}>تواصل مع فريقنا مباشرة</div>
            <a href="https://wa.me/9660570131122" style={{
              display:'flex', alignItems:'center', justifyContent:'center', gap:8, padding:'10px 16px',
              background:'var(--accent)', color:'var(--indigo-deep)', borderRadius:'var(--r-1)',
              fontSize:13, fontWeight:600
            }}>واتساب 💬</a>
          </div>
        </aside>

        {/* Q&A */}
        <main style={{display:'flex', flexDirection:'column', gap:48}}>
          {sections.map(({cat,icon,items},si)=>(
            <div key={si} id={`section-${si}`}>
              <div style={{display:'flex', gap:12, alignItems:'center', marginBottom:24}}>
                <span style={{fontSize:22}}>{icon}</span>
                <h2 style={{margin:0, fontSize:20, fontWeight:700, color:'var(--indigo)'}}>{cat}</h2>
              </div>
              <div style={{display:'flex', flexDirection:'column', gap:8}}>
                {items.map(({q,a},qi)=>{
                  const idx = globalIdx++;
                  return (
                    <div key={qi} style={{
                      background:'var(--surface)', border:'1px solid var(--rule)',
                      borderRadius:'var(--r-2)', overflow:'hidden'
                    }}>
                      <button onClick={()=>toggle(idx)} style={{
                        width:'100%', padding:'18px 24px', display:'flex',
                        justifyContent:'space-between', alignItems:'center', gap:16,
                        textAlign:'start', cursor:'pointer', fontFamily:'var(--f-ar)',
                        fontWeight:600, fontSize:15, color:'var(--indigo)'
                      }}>
                        <span>{q}</span>
                        <span style={{
                          fontFamily:'var(--f-mono)', fontSize:18, color:'var(--accent-deep)',
                          transform: open===idx ? 'rotate(45deg)' : 'none', transition:'transform .2s',
                          flexShrink:0
                        }}>+</span>
                      </button>
                      {open===idx && (
                        <div style={{padding:'0 24px 20px', fontSize:14, lineHeight:1.75, color:'var(--ink-3)', borderTop:'1px solid var(--rule-soft)'}}>
                          {a}
                        </div>
                      )}
                    </div>
                  );
                })}
              </div>
            </div>
          ))}
        </main>
      </div>
      <Footer/>
    </div>
  );
};
window.FAQ = FAQ;
