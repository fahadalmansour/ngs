// Returns & Refunds — NeoGen Store
const Returns = () => {
  const [step, setStep] = React.useState(1);
  const [reason, setReason] = React.useState('');

  return (
    <div dir="rtl" style={{background:'var(--bg)', minHeight:'100%', fontFamily:'var(--f-ar)'}}>
      <TopBar/>
      <Header/>

      <section style={{borderBottom:'1px solid var(--rule)'}}>
        <div style={{maxWidth:1440, margin:'0 auto', padding:'56px 48px 40px'}}>
          <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:16}}>الرئيسية / الإرجاع</div>
          <h1 className="t-h1" style={{margin:'0 0 12px'}}>الإرجاع والاستبدال</h1>
          <p className="t-body" style={{margin:0, maxWidth:520}}>14 يوماً لإرجاع أي منتج بدون أسئلة — شرط أن يكون بحالته الأصلية.</p>
        </div>
      </section>

      <div style={{maxWidth:1440, margin:'0 auto', padding:'48px 48px 96px', display:'grid', gridTemplateColumns:'1fr 380px', gap:48, alignItems:'start'}}>

        <div style={{display:'flex', flexDirection:'column', gap:40}}>

          {/* Policy summary */}
          <div>
            <SectionHeader n={1} eyebrow="Return Policy" title="سياسة الإرجاع"/>
            <div style={{display:'grid', gridTemplateColumns:'1fr 1fr', gap:16, marginTop:24}}>
              {[
                {icon:'📅', title:'مدة الإرجاع', value:'14 يوم', sub:'من تاريخ استلام الطلب', color:'var(--good)'},
                {icon:'📦', title:'حالة المنتج', value:'أصلي', sub:'غير مستخدم مع كامل الملحقات', color:'var(--accent-deep)'},
                {icon:'💳', title:'الاسترداد', value:'3–7 أيام', sub:'لنفس وسيلة الدفع الأصلية', color:'var(--warn)'},
                {icon:'🚚', title:'رسوم الشحن', value:'على حسب', sub:'مجاني إذا كان الخطأ منّا', color:'var(--ink-4)'},
              ].map(({icon,title,value,sub,color},i)=>(
                <div key={i} style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', padding:24}}>
                  <div style={{fontSize:22, marginBottom:10}}>{icon}</div>
                  <div style={{fontFamily:'var(--f-mono)', fontSize:10, textTransform:'uppercase', letterSpacing:'0.08em', color:'var(--dim)', marginBottom:4}}>{title}</div>
                  <div style={{fontFamily:'var(--f-wordmark)', fontSize:22, fontWeight:700, color, marginBottom:4}}>{value}</div>
                  <div style={{fontSize:12, color:'var(--ink-4)'}}>{sub}</div>
                </div>
              ))}
            </div>
          </div>

          {/* What's eligible */}
          <div>
            <SectionHeader n={2} eyebrow="Eligibility" title="ما يقبل الإرجاع وما لا يقبل"/>
            <div style={{display:'grid', gridTemplateColumns:'1fr 1fr', gap:16, marginTop:24}}>
              <div style={{background:'rgba(34,197,94,0.05)', border:'1px solid rgba(34,197,94,0.2)', borderRadius:'var(--r-2)', padding:24}}>
                <div style={{fontFamily:'var(--f-mono)', fontSize:11, textTransform:'uppercase', letterSpacing:'0.08em', color:'var(--good)', marginBottom:16}}>✓ يقبل الإرجاع</div>
                <div style={{display:'flex', flexDirection:'column', gap:10}}>
                  {[
                    'منتج بحالته الأصلية مع الكرتون والملحقات',
                    'منتج معيب أو لا يطابق الوصف',
                    'منتج وصل تالفاً',
                    'خطأ في الطلب من طرفنا',
                    'منتج لم يُستخدم إطلاقاً',
                  ].map((it,i)=>(
                    <div key={i} style={{display:'flex', gap:8, fontSize:13, color:'var(--ink-3)'}}>
                      <span style={{color:'var(--good)', fontWeight:700, flexShrink:0}}>✓</span> {it}
                    </div>
                  ))}
                </div>
              </div>
              <div style={{background:'rgba(239,68,68,0.05)', border:'1px solid rgba(239,68,68,0.2)', borderRadius:'var(--r-2)', padding:24}}>
                <div style={{fontFamily:'var(--f-mono)', fontSize:11, textTransform:'uppercase', letterSpacing:'0.08em', color:'var(--sale)', marginBottom:16}}>✕ لا يقبل الإرجاع</div>
                <div style={{display:'flex', flexDirection:'column', gap:10}}>
                  {[
                    'بطاقات رقمية مفعّلة أو مكشوف رمزها',
                    'منتج تالف بسبب الاستخدام الخاطئ',
                    'طلبات مضى عليها أكثر من 14 يوماً',
                    'منتجات مفتوحة ومُركَّبة بدون عيب',
                    'منتجات يُطلب فيها "تغيير رأي" بعد الاستخدام',
                  ].map((it,i)=>(
                    <div key={i} style={{display:'flex', gap:8, fontSize:13, color:'var(--ink-3)'}}>
                      <span style={{color:'var(--sale)', fontWeight:700, flexShrink:0}}>✕</span> {it}
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </div>

          {/* Steps */}
          <div>
            <SectionHeader n={3} eyebrow="How It Works" title="خطوات الإرجاع"/>
            <div style={{display:'flex', flexDirection:'column', gap:0, marginTop:24, position:'relative'}}>
              <div style={{position:'absolute', right:20, top:24, bottom:24, width:2, background:'var(--rule)'}}/>
              {[
                {n:'01', title:'تواصل معنا', body:'أرسل رقم طلبك وسبب الإرجاع عبر واتساب أو صفحة الدعم.', time:'خلال دقائق'},
                {n:'02', title:'الموافقة والتعليمات', body:'سيراجع فريقنا طلبك ويرسل لك تعليمات الشحن أو يرتب الاستلام.', time:'خلال ساعة عمل'},
                {n:'03', title:'إرسال المنتج', body:'غلّف المنتج بشكل جيد وأرسله للعنوان المحدد. احتفظ بإيصال الشحن.', time:'خلال 3 أيام'},
                {n:'04', title:'الفحص والاسترداد', body:'عند وصول المنتج وتأكيد حالته، يُعالج الاسترداد فوراً.', time:'3–7 أيام عمل'},
              ].map(({n,title,body,time},i)=>(
                <div key={i} style={{display:'flex', gap:24, paddingBottom:32, paddingRight:48, position:'relative'}}>
                  <div style={{
                    position:'absolute', right:0, top:0,
                    width:40, height:40, borderRadius:'50%',
                    background:'var(--indigo)', color:'#fff',
                    display:'flex', alignItems:'center', justifyContent:'center',
                    fontFamily:'var(--f-mono)', fontWeight:700, fontSize:12, flexShrink:0,
                    zIndex:1
                  }}>{n}</div>
                  <div style={{flex:1}}>
                    <div style={{display:'flex', justifyContent:'space-between', alignItems:'baseline', gap:16, marginBottom:6}}>
                      <div style={{fontWeight:700, fontSize:15, color:'var(--indigo)'}}>{title}</div>
                      <div style={{fontFamily:'var(--f-mono)', fontSize:10, color:'var(--accent-deep)', textTransform:'uppercase', letterSpacing:'0.06em', flexShrink:0}}>{time}</div>
                    </div>
                    <div style={{fontSize:13, lineHeight:1.65, color:'var(--ink-4)'}}>{body}</div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Return form */}
        <div style={{position:'sticky', top:24}}>
          <div style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', overflow:'hidden', boxShadow:'var(--shadow-md)'}}>
            <div style={{background:'var(--indigo)', padding:'20px 24px'}}>
              <div style={{fontFamily:'var(--f-mono)', fontSize:10, textTransform:'uppercase', letterSpacing:'0.1em', color:'rgba(255,255,255,0.4)', marginBottom:4}}>ابدأ الإرجاع</div>
              <div style={{fontWeight:700, fontSize:18, color:'#fff'}}>طلب إرجاع جديد</div>
            </div>
            <div style={{padding:24, display:'flex', flexDirection:'column', gap:16}}>
              <div>
                <label style={{display:'block', fontSize:12, fontWeight:600, color:'var(--ink-4)', marginBottom:6, fontFamily:'var(--f-mono)', textTransform:'uppercase', letterSpacing:'0.06em'}}>رقم الطلب</label>
                <input placeholder="NG-2026-XXXXX" dir="ltr" style={{
                  width:'100%', padding:'11px 14px', border:'1px solid var(--rule-strong)',
                  borderRadius:'var(--r-1)', background:'var(--bg)', fontFamily:'var(--f-mono)',
                  fontSize:13, color:'var(--ink)', outline:'none', boxSizing:'border-box'
                }}/>
              </div>
              <div>
                <label style={{display:'block', fontSize:12, fontWeight:600, color:'var(--ink-4)', marginBottom:6, fontFamily:'var(--f-mono)', textTransform:'uppercase', letterSpacing:'0.06em'}}>سبب الإرجاع</label>
                <select value={reason} onChange={e=>setReason(e.target.value)} style={{
                  width:'100%', padding:'11px 14px', border:'1px solid var(--rule-strong)',
                  borderRadius:'var(--r-1)', background:'var(--bg)', fontFamily:'var(--f-ar)',
                  fontSize:13, color:'var(--ink)', outline:'none'
                }}>
                  <option value="">اختر سبباً</option>
                  <option>المنتج لا يطابق الوصف</option>
                  <option>وصل المنتج تالفاً</option>
                  <option>خطأ في الطلب</option>
                  <option>منتج معيب من المصنع</option>
                  <option>تغيير رأي</option>
                  <option>أخرى</option>
                </select>
              </div>
              <div>
                <label style={{display:'block', fontSize:12, fontWeight:600, color:'var(--ink-4)', marginBottom:6, fontFamily:'var(--f-mono)', textTransform:'uppercase', letterSpacing:'0.06em'}}>تفاصيل إضافية</label>
                <textarea rows={3} placeholder="صِف المشكلة بإيجاز..." style={{
                  width:'100%', padding:'11px 14px', border:'1px solid var(--rule-strong)',
                  borderRadius:'var(--r-1)', background:'var(--bg)', fontFamily:'var(--f-ar)',
                  fontSize:13, color:'var(--ink)', outline:'none', resize:'vertical', boxSizing:'border-box'
                }}/>
              </div>
              <button className="btn" style={{borderRadius:'var(--r-2)', width:'100%', justifyContent:'center', padding:14}}>
                إرسال طلب الإرجاع →
              </button>
              <div style={{textAlign:'center', fontFamily:'var(--f-mono)', fontSize:10, color:'var(--dim)', textTransform:'uppercase', letterSpacing:'0.07em'}}>
                أو تواصل عبر واتساب 0570131122
              </div>
            </div>
          </div>
        </div>
      </div>
      <Footer/>
    </div>
  );
};
window.Returns = Returns;
