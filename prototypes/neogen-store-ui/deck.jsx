// Pitch deck — 6 slides showcasing brand & strategy
const PitchDeck = () => {
  const Slide = ({n, total=6, label, children, dark}) => (
    <section style={{
      width: 1280, height: 720, background: dark?'var(--ink)':'var(--paper)', color: dark?'var(--paper)':'var(--ink)',
      padding: 56, position:'relative', display:'flex', flexDirection:'column', overflow:'hidden',
      borderBottom:'1px solid '+(dark?'var(--ink-3)':'var(--rule)')
    }} dir="rtl">
      <div style={{display:'flex', justifyContent:'space-between', marginBottom:32}}>
        <div className="mono-up" style={{color: dark?'var(--paper-3)':'var(--ink-4)'}}>NEOGEN STORE · 2026 · {label}</div>
        <div className="mono-up" style={{color: dark?'var(--paper-3)':'var(--ink-4)'}}>{String(n).padStart(2,'0')} / {String(total).padStart(2,'0')}</div>
      </div>
      <div style={{flex:1}}>{children}</div>
      <div style={{display:'flex', justifyContent:'space-between', marginTop:24, paddingTop:16, borderTop:'1px solid '+(dark?'var(--ink-3)':'var(--rule)')}}>
        <div className="mono-up" style={{color: dark?'var(--paper-3)':'var(--ink-4)'}}>متجر تقني سعودي · GCC</div>
        <div style={{display:'flex', gap:8, alignItems:'center'}}>
          <div style={{width:20, height:20, background: dark?'var(--paper)':'var(--ink)', color: dark?'var(--ink)':'var(--paper)', display:'grid', placeItems:'center', fontFamily:'var(--f-mono)', fontSize:9, fontWeight:700}}>NG</div>
          <span className="mono-up">NEOGEN.STORE</span>
        </div>
      </div>
    </section>
  );

  return (
    <div style={{background:'var(--paper-2)', padding:'40px 0', display:'flex', flexDirection:'column', alignItems:'center', gap:24}}>
      {/* 01 — Title */}
      <Slide n={1} label="COVER" dark>
        <div style={{display:'flex', flexDirection:'column', justifyContent:'center', height:'100%', maxWidth:900}}>
          <div className="mono-up" style={{color:'var(--paper-3)', marginBottom:32}}>عرض تقديمي · INVESTOR DECK · Q2 2026</div>
          <h1 style={{fontSize:120, lineHeight:0.9, fontWeight:600, margin:0, letterSpacing:'-0.02em', textWrap:'balance'}}>
            جيل التقنية<br/>القادم.
          </h1>
          <div className="arabesque-rule" style={{margin:'48px 0 32px', maxWidth:300, filter:'invert(1)', opacity:0.4}}></div>
          <p style={{fontSize:22, color:'var(--paper-2)', maxWidth:640, lineHeight:1.5}}>
            متجر تقني سعودي لمحترفي الشبكات، الهوم لاب، البيوت الذكية، والألعاب. شحن من المملكة لكل دول الخليج.
          </p>
        </div>
      </Slide>

      {/* 02 — Problem */}
      <Slide n={2} label="01 · المشكلة">
        <div style={{display:'grid', gridTemplateColumns:'1fr 1.4fr', gap:64, height:'100%'}}>
          <div>
            <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:16}}>01 · المشكلة</div>
            <h2 className="t-h1" style={{margin:0, fontSize:64}}>المشغّل المحترف في الخليج بلا متجر يفهمه.</h2>
          </div>
          <div style={{display:'flex', flexDirection:'column', justifyContent:'center', gap:24}}>
            {[
              ['متاجر عامة','تبيع كل شيء، لا تختار، لا تختبر.'],
              ['طلب من الخارج','أسابيع شحن، جمارك، بدون ضمان محلي.'],
              ['بائعون أفراد','بدون فاتورة، بدون ضريبة، بدون دعم.']
            ].map(([t,d],i)=>(
              <div key={i} style={{borderBottom:'1px solid var(--rule)', paddingBottom:20, display:'grid', gridTemplateColumns:'40px 1fr', gap:20}}>
                <span className="mono-up" style={{color:'var(--ink-4)', paddingTop:6}}>{String(i+1).padStart(2,'0')}</span>
                <div>
                  <h3 style={{margin:0, fontSize:24, fontWeight:600}}>{t}</h3>
                  <p style={{margin:'6px 0 0', fontSize:16, color:'var(--ink-3)'}}>{d}</p>
                </div>
              </div>
            ))}
          </div>
        </div>
      </Slide>

      {/* 03 — Solution */}
      <Slide n={3} label="02 · الحل">
        <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:16}}>02 · الحل</div>
        <h2 className="t-h1" style={{margin:'0 0 48px', fontSize:64, maxWidth:900, textWrap:'balance'}}>كتالوج مختار. خدمة محلية. مواصفات مفهومة.</h2>
        <div style={{display:'grid', gridTemplateColumns:'repeat(3,1fr)', gap:0, border:'1px solid var(--ink)'}}>
          {[
            {n:'01', t:'كل وحدة مختارة', d:'٢١٥ منتج عبر ٦ فئات. كل وحدة اختبرناها قبل الإضافة. لا حشو، لا تكرار.'},
            {n:'02', t:'شحن من المملكة', d:'مستودعات في الرياض. شحن 2–5 أيام لكل دول الخليج. SMSA · Aramex · DHL.'},
            {n:'03', t:'دعم المشغّل', d:'مكتب خدمة لتنفيذ الشبكات والبيوت الذكية. تركيب وضبط بالموقع.'}
          ].map((s,i)=>(
            <div key={i} style={{padding:32, borderInlineEnd: i<2?'1px solid var(--ink)':'none', minHeight:360, display:'flex', flexDirection:'column', gap:16}}>
              <span className="mono-up" style={{color:'var(--ink-4)'}}>{s.n}</span>
              <h3 style={{margin:0, fontSize:28, fontWeight:600}}>{s.t}</h3>
              <p style={{margin:0, fontSize:15, color:'var(--ink-3)', lineHeight:1.6}}>{s.d}</p>
            </div>
          ))}
        </div>
      </Slide>

      {/* 04 — Market */}
      <Slide n={4} label="03 · السوق">
        <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:16}}>03 · السوق · GCC TAM</div>
        <h2 className="t-h1" style={{margin:'0 0 48px', fontSize:64}}>سوق الخليج. ٦ دول. مشغّل واحد.</h2>
        <div style={{display:'grid', gridTemplateColumns:'1.4fr 1fr', gap:48}}>
          <div style={{display:'grid', gridTemplateColumns:'repeat(3,1fr)', gap:0, border:'1px solid var(--rule)'}}>
            {[
              ['SA','السعودية','36M','الأكبر'],
              ['AE','الإمارات','10M','الأعلى إنفاقاً'],
              ['KW','الكويت','4.3M','-'],
              ['QA','قطر','3M','-'],
              ['OM','عُمان','5.1M','-'],
              ['BH','البحرين','1.7M','-'],
            ].map(([c,n,p,note],i)=>(
              <div key={i} style={{padding:24, borderInlineEnd: (i%3<2)?'1px solid var(--rule)':'none', borderBottom: i<3?'1px solid var(--rule)':'none', minHeight:140}}>
                <div style={{display:'flex', justifyContent:'space-between'}}>
                  <span className="en mono-up" style={{fontSize:24, fontWeight:700}}>{c}</span>
                  {note!=='-' && <span className="chip chip-accent">{note}</span>}
                </div>
                <div style={{fontSize:18, fontWeight:500, marginTop:12}}>{n}</div>
                <div className="en" style={{fontSize:14, color:'var(--ink-4)', marginTop:4}}>~{p} pop</div>
              </div>
            ))}
          </div>
          <div style={{display:'flex', flexDirection:'column', gap:24, justifyContent:'center'}}>
            {[['60M+','إجمالي السكان'],['$45B','سوق الإلكترونيات'],['72%','معدل اعتماد التقنية'],['18-45','الفئة المستهدفة']].map(([n,l],i)=>(
              <div key={i} style={{borderBottom:'1px solid var(--rule)', paddingBottom:12}}>
                <div className="en" style={{fontSize:48, fontWeight:600, lineHeight:1}}>{n}</div>
                <div className="mono-up" style={{color:'var(--ink-4)', marginTop:6}}>{l}</div>
              </div>
            ))}
          </div>
        </div>
      </Slide>

      {/* 05 — Traction */}
      <Slide n={5} label="04 · النتائج">
        <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:16}}>04 · النتائج · 6M</div>
        <h2 className="t-h1" style={{margin:'0 0 48px', fontSize:64}}>النصف الأول. الأرقام تتكلّم.</h2>
        <div style={{display:'grid', gridTemplateColumns:'repeat(4,1fr)', gap:0, border:'1px solid var(--ink)', background:'var(--ink)', color:'var(--paper)'}}>
          {[
            ['215','منتج في الكتالوج','+40% Q/Q'],
            ['16','علامة موثّقة','أصلية'],
            ['1,247','طلب مكتمل','98% رضا'],
            ['6/6','دول الخليج','مغطّاة']
          ].map(([n,l,sub],i)=>(
            <div key={i} style={{padding:32, borderInlineEnd: i<3?'1px solid var(--ink-3)':'none', minHeight:200}}>
              <div className="en" style={{fontSize:64, fontWeight:600, lineHeight:1, letterSpacing:'-0.02em'}}>{n}</div>
              <div style={{fontSize:15, marginTop:12, color:'var(--paper)'}}>{l}</div>
              <div className="mono-up" style={{color:'var(--paper-3)', marginTop:6}}>{sub}</div>
            </div>
          ))}
        </div>
        <div style={{marginTop:32, padding:24, border:'1px solid var(--rule)', display:'grid', gridTemplateColumns:'repeat(6,1fr)', gap:24}}>
          {[['Jan','120'],['Feb','156'],['Mar','198'],['Apr','234'],['May','267'],['Jun','272']].map(([m,v],i)=>(
            <div key={i}>
              <div style={{height: parseInt(v)/3, background:'var(--ink)', marginBottom:8}}></div>
              <div className="mono-up" style={{color:'var(--ink-4)'}}>{m} 26</div>
              <div className="en" style={{fontSize:18, fontWeight:600}}>{v}</div>
            </div>
          ))}
        </div>
      </Slide>

      {/* 06 — Closing */}
      <Slide n={6} total={6} label="CLOSE" dark>
        <div style={{display:'flex', flexDirection:'column', justifyContent:'space-between', height:'100%'}}>
          <div className="mono-up" style={{color:'var(--paper-3)'}}>06 · CLOSING</div>
          <div>
            <h2 style={{fontSize:96, lineHeight:0.95, margin:0, fontWeight:600, letterSpacing:'-0.02em', textWrap:'balance'}}>
              نبني البنية التقنية للخليج.<br/>
              <span style={{color:'var(--paper-3)'}}>مشغّل واحد. ست دول.</span>
            </h2>
            <div style={{display:'grid', gridTemplateColumns:'repeat(3,1fr)', gap:0, border:'1px solid var(--ink-3)', marginTop:64}}>
              {[
                ['تواصل','support@neogen.store'],
                ['واتساب','+966 57 013 1122'],
                ['الموقع','neogen.store']
              ].map(([k,v],i)=>(
                <div key={i} style={{padding:24, borderInlineEnd: i<2?'1px solid var(--ink-3)':'none'}}>
                  <div className="mono-up" style={{color:'var(--paper-3)', marginBottom:8}}>{k}</div>
                  <div className="en" style={{fontSize:18, fontWeight:500}}>{v}</div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </Slide>
    </div>
  );
};
window.PitchDeck = PitchDeck;
