// Warranty — NeoGen Store
const Warranty = () => (
  <div dir="rtl" style={{background:'var(--bg)', minHeight:'100%', fontFamily:'var(--f-ar)'}}>
    <TopBar/>
    <Header/>

    <section style={{borderBottom:'1px solid var(--rule)'}}>
      <div style={{maxWidth:1440, margin:'0 auto', padding:'56px 48px 40px'}}>
        <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:16}}>الرئيسية / الضمان</div>
        <h1 className="t-h1" style={{margin:'0 0 12px'}}>الضمان والصيانة</h1>
        <p className="t-body" style={{margin:0, maxWidth:520}}>ضمان المصنع الأصلي + دعم محلي. نساعدك في كل خطوة.</p>
      </div>
    </section>

    <div style={{maxWidth:1440, margin:'0 auto', padding:'48px 48px 96px', display:'flex', flexDirection:'column', gap:56}}>

      {/* Coverage cards */}
      <div>
        <SectionHeader n={1} eyebrow="Warranty Coverage" title="ماذا يغطي الضمان؟"/>
        <div style={{display:'grid', gridTemplateColumns:'repeat(3,1fr)', gap:20, marginTop:32}}>
          {[
            {icon:'✓', color:'var(--good)', bg:'rgba(34,197,94,0.06)', border:'rgba(34,197,94,0.2)',
              title:'مشمول في الضمان', items:[
              'عيوب تصنيع أو مواد',
              'أعطال تشغيلية مفاجئة',
              'مشاكل البرمجيات الداخلية (Firmware)',
              'قطع غيار معيبة من المصنع',
              'توقف المنتج عن العمل في الظروف الاعتيادية',
            ]},
            {icon:'✕', color:'var(--sale)', bg:'rgba(239,68,68,0.05)', border:'rgba(239,68,68,0.2)',
              title:'غير مشمول في الضمان', items:[
              'أضرار ناتجة عن السقوط أو الصدمة',
              'تلف الماء أو الرطوبة',
              'الاستخدام في بيئة غير مناسبة',
              'التعديل أو الفتح من غير مختص',
              'الحوادث أو الإهمال',
            ]},
            {icon:'⚡', color:'var(--warn)', bg:'rgba(245,158,11,0.06)', border:'rgba(245,158,11,0.2)',
              title:'ضمان موسّع (اختياري)', items:[
              'تمديد الضمان لسنة إضافية',
              'تغطية الحوادث العرضية',
              'أولوية في الدعم الفني',
              'استبدال سريع دون انتظار',
              'تاح لمنتجات مختارة — استفسر',
            ]},
          ].map(({icon,color,bg,border,title,items},i)=>(
            <div key={i} style={{background:bg, border:`1px solid ${border}`, borderRadius:'var(--r-2)', padding:28}}>
              <div style={{fontFamily:'var(--f-mono)', fontSize:11, textTransform:'uppercase', letterSpacing:'0.08em', color, marginBottom:16}}>{icon} {title}</div>
              <div style={{display:'flex', flexDirection:'column', gap:10}}>
                {items.map((it,j)=>(
                  <div key={j} style={{display:'flex', gap:8, fontSize:13, color:'var(--ink-3)'}}>
                    <span style={{color, flexShrink:0, fontWeight:700}}>{icon}</span> {it}
                  </div>
                ))}
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Duration by brand */}
      <div>
        <SectionHeader n={2} eyebrow="Warranty Periods" title="مدد الضمان حسب العلامة التجارية"/>
        <div style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', overflow:'hidden', marginTop:32}}>
          <div style={{display:'grid', gridTemplateColumns:'2fr 1fr 1fr', borderBottom:'1px solid var(--rule)', background:'var(--surface-2)', padding:'12px 24px'}}>
            {['العلامة التجارية','مدة الضمان','نوع الضمان'].map((h,i)=>(
              <div key={i} style={{fontFamily:'var(--f-mono)', fontSize:10, textTransform:'uppercase', letterSpacing:'0.08em', color:'var(--ink-4)'}}>{h}</div>
            ))}
          </div>
          {[
            ['Ubiquiti / UniFi','سنة واحدة (12 شهراً)','ضمان المصنع'],
            ['TP-Link / Omada','سنتان (24 شهراً)','ضمان المصنع'],
            ['Aqara','سنة واحدة','ضمان المصنع'],
            ['MinisForum','سنة واحدة','ضمان المصنع'],
            ['NVIDIA (بطاقات)','3 سنوات','ضمان المصنع'],
            ['DJI','12 شهراً','ضمان DJI Care'],
            ['Elgato','2 سنة','ضمان المصنع'],
            ['Prusa','2 سنة','ضمان المصنع'],
            ['بطاقات رقمية','لا ينطبق','الكود يستبدل مرة واحدة إذا لم يعمل'],
          ].map(([brand,period,type],i)=>(
            <div key={i} style={{
              display:'grid', gridTemplateColumns:'2fr 1fr 1fr',
              padding:'14px 24px', borderBottom:'1px solid var(--rule-soft)',
              background: i%2===0?'var(--surface)':'var(--surface-2)'
            }}>
              <div style={{fontWeight:600, fontSize:14, color:'var(--indigo)'}}>{brand}</div>
              <div style={{fontFamily:'var(--f-mono)', fontSize:13, color:'var(--accent-deep)'}}>{period}</div>
              <div style={{fontSize:13, color:'var(--ink-4)'}}>{type}</div>
            </div>
          ))}
        </div>
      </div>

      {/* Claim process */}
      <div>
        <SectionHeader n={3} eyebrow="Claim Process" title="كيف تطالب بالضمان؟"/>
        <div style={{display:'grid', gridTemplateColumns:'repeat(4,1fr)', gap:16, marginTop:32}}>
          {[
            {n:'01', title:'تواصل معنا', body:'أرسل وصف المشكلة وصور أو فيديو توضيحي عبر واتساب أو تذكرة دعم.'},
            {n:'02', title:'التقييم التقني', body:'فريقنا التقني يقيّم المشكلة ويحدد إذا كانت مشمولة في الضمان خلال يوم عمل.'},
            {n:'03', title:'الإرسال للصيانة', body:'ترتّب نيوجن الشحن إلى مركز الصيانة أو العلامة التجارية وتتابع العملية نيابةً عنك.'},
            {n:'04', title:'الإصلاح أو الاستبدال', body:'بعد الإصلاح أو الاستبدال، يُعاد إليك المنتج مع ضمان على الإصلاح ذاته.'},
          ].map(({n,title,body},i)=>(
            <div key={i} style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', padding:24}}>
              <div style={{fontFamily:'var(--f-mono)', fontSize:22, fontWeight:700, color:'var(--rule-strong)', marginBottom:12}}>{n}</div>
              <div style={{fontWeight:700, fontSize:14, color:'var(--indigo)', marginBottom:8}}>{title}</div>
              <div style={{fontSize:13, lineHeight:1.65, color:'var(--ink-4)'}}>{body}</div>
            </div>
          ))}
        </div>
        <div style={{marginTop:24, display:'flex', gap:12}}>
          <button className="btn" style={{borderRadius:'var(--r-2)'}}>فتح تذكرة ضمان</button>
          <a href="https://wa.me/9660570131122" style={{
            display:'inline-flex', alignItems:'center', padding:'12px 22px',
            border:'1px solid var(--rule-strong)', borderRadius:'var(--r-2)',
            fontSize:14, fontWeight:600, color:'var(--ink)'
          }}>واتساب مباشر</a>
        </div>
      </div>
    </div>
    <Footer/>
  </div>
);
window.Warranty = Warranty;
