// Login / Signup page
const Auth = () => {
  const [mode, setMode] = React.useState('login'); // 'login' | 'signup' | 'otp'
  const [method, setMethod] = React.useState('phone'); // 'email' | 'phone'

  return (
    <div dir="rtl" style={{background:'var(--bg)', minHeight:'100%', fontFamily:'var(--f-ar)'}}>
      <TopBar/>
      {/* Slim header */}
      <header style={{borderBottom:'1px solid var(--rule)', background:'rgba(248,250,252,0.95)', backdropFilter:'blur(12px)'}}>
        <div style={{maxWidth:1440, margin:'0 auto', padding:'16px 48px', display:'flex', justifyContent:'space-between', alignItems:'center'}}>
          <Logo size={32}/>
          <div style={{display:'flex', gap:8}}>
            <button onClick={()=>setMode('login')} className={mode==='login'?'btn btn-dark btn-sm':'btn btn-ghost btn-sm'} style={{borderRadius:'var(--r-2)'}}>تسجيل الدخول</button>
            <button onClick={()=>setMode('signup')} className={mode==='signup'?'btn btn-sm':'btn btn-ghost btn-sm'} style={{borderRadius:'var(--r-2)'}}>إنشاء حساب</button>
          </div>
        </div>
      </header>

      <div style={{
        minHeight:'calc(100vh - 120px)',
        display:'grid', gridTemplateColumns:'1fr 1fr', alignItems:'stretch'
      }}>
        {/* Left — brand panel */}
        <div style={{
          background:`linear-gradient(135deg, var(--indigo-deep) 0%, var(--indigo) 100%)`,
          padding:'80px 64px', display:'flex', flexDirection:'column', justifyContent:'space-between',
          position:'relative', overflow:'hidden'
        }}>
          <div style={{
            position:'absolute', inset:0, opacity:0.06,
            background:'linear-gradient(rgba(255,255,255,0.2) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,0.2) 1px,transparent 1px)',
            backgroundSize:'48px 48px'
          }}/>
          {/* Accent glow */}
          <div style={{position:'absolute', top:-100, insetInlineEnd:-100, width:400, height:400, borderRadius:'50%', background:'rgba(56,189,248,0.08)', filter:'blur(60px)'}}/>

          <div style={{position:'relative'}}>
            <Logo size={36} inverted/>
          </div>

          <div style={{position:'relative'}}>
            <h2 style={{
              fontFamily:'var(--f-wordmark)', fontSize:56, fontWeight:700, lineHeight:0.95,
              letterSpacing:'-0.02em', color:'#fff', margin:'0 0 24px'
            }}>
              {mode==='signup' ? <>انضم لجيل<br/><span style={{color:'var(--accent)'}}>التقنية.</span></> : <>مرحباً<br/><span style={{color:'var(--accent)'}}>بعودتك.</span></>}
            </h2>
            <div style={{width:48, height:3, background:'var(--accent)', borderRadius:2, marginBottom:24}}/>
            <p style={{fontSize:16, color:'rgba(255,255,255,0.6)', lineHeight:1.65, maxWidth:380, margin:'0 0 40px'}}>
              {mode==='signup'
                ? 'متجر تقني سعودي لمحترفي الشبكات، الهوم لاب، البيوت الذكية، والألعاب. شحن لكل دول الخليج.'
                : 'سجّل دخولك للوصول إلى طلباتك، بطاقاتك الرقمية، ضماناتك، والمفضلة.'}
            </p>
            {/* Benefits */}
            <div style={{display:'flex', flexDirection:'column', gap:12}}>
              {[
                ['🎁','بطاقاتك الرقمية وأكوادها محفوظة'],
                ['🛡️','تتبع الضمانات لكل منتجاتك'],
                ['📦','تتبع شحناتك في الوقت الفعلي'],
                ['⚡','دفع أسرع بطرق دفع محفوظة'],
              ].map(([icon,text],i)=>(
                <div key={i} style={{display:'flex', gap:12, alignItems:'center'}}>
                  <span style={{fontSize:18}}>{icon}</span>
                  <span style={{fontSize:14, color:'rgba(255,255,255,0.7)'}}>{text}</span>
                </div>
              ))}
            </div>
          </div>

          {/* GCC flags */}
          <div style={{position:'relative', display:'flex', gap:8, alignItems:'center'}}>
            <span style={{fontFamily:'var(--f-mono)', fontSize:10, textTransform:'uppercase', letterSpacing:'0.08em', color:'rgba(255,255,255,0.3)'}}>GCC:</span>
            {['🇸🇦','🇦🇪','🇰🇼','🇧🇭','🇴🇲','🇶🇦'].map((f,i)=><span key={i} style={{fontSize:18}}>{f}</span>)}
          </div>
        </div>

        {/* Right — form */}
        <div style={{padding:'80px 64px', display:'flex', alignItems:'center', justifyContent:'center', background:'var(--bg)'}}>
          <div style={{width:'100%', maxWidth:440}}>

            {/* OTP mode */}
            {mode === 'otp' ? (
              <div>
                <button onClick={()=>setMode('login')} style={{fontFamily:'var(--f-ar)', fontSize:13, color:'var(--ink-4)', marginBottom:32, display:'flex', gap:6, alignItems:'center'}}>← رجوع</button>
                <div className="mono-up" style={{color:'var(--dim)', marginBottom:12}}>التحقق · VERIFY OTP</div>
                <h2 className="t-h2" style={{margin:'0 0 8px', color:'var(--indigo)'}}>أدخل رمز التحقق</h2>
                <p style={{fontSize:14, color:'var(--ink-3)', margin:'0 0 32px'}}>أرسلنا رمزاً مكوناً من 6 أرقام إلى +966 50 ••• ••34</p>
                <div style={{display:'flex', gap:10, justifyContent:'center', marginBottom:28}}>
                  {[...Array(6)].map((_,i)=>(
                    <input key={i} maxLength={1} style={{
                      width:52, height:60, textAlign:'center', fontSize:24, fontFamily:'var(--f-wordmark)', fontWeight:700,
                      border:'2px solid '+(i===0?'var(--accent)':'var(--rule)'),
                      borderRadius:'var(--r-2)', background:'var(--surface)', color:'var(--indigo)',
                      outline:'none'
                    }}/>
                  ))}
                </div>
                <button className="btn" style={{width:'100%', justifyContent:'center', borderRadius:'var(--r-2)', padding:16}}>تأكيد الرمز</button>
                <div style={{textAlign:'center', marginTop:16, fontSize:13, color:'var(--dim)'}}>
                  لم تستلم الرمز؟ <a href="#" style={{color:'var(--accent-deep)', fontWeight:600}}>إعادة الإرسال</a>
                </div>
              </div>
            ) : (
              <div>
                <div className="mono-up" style={{color:'var(--dim)', marginBottom:12}}>
                  {mode==='signup' ? 'إنشاء حساب · SIGN UP' : 'تسجيل الدخول · SIGN IN'}
                </div>
                <h2 className="t-h2" style={{margin:'0 0 32px', color:'var(--indigo)'}}>
                  {mode==='signup' ? 'أنشئ حسابك' : 'سجّل دخولك'}
                </h2>

                {/* Social auth */}
                <div style={{display:'flex', flexDirection:'column', gap:10, marginBottom:28}}>
                  {/* Google */}
                  <button style={{
                    display:'flex', alignItems:'center', justifyContent:'center', gap:12,
                    padding:'13px 20px', border:'1px solid var(--rule-strong)',
                    borderRadius:'var(--r-2)', background:'var(--surface)', cursor:'pointer',
                    fontFamily:'var(--f-ar)', fontSize:15, fontWeight:500, color:'var(--ink)',
                    boxShadow:'var(--shadow-sm)', transition:'box-shadow .15s, transform .15s'
                  }}>
                    <svg width="20" height="20" viewBox="0 0 24 24">
                      <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                      <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                      <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                      <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    المتابعة بحساب Google
                  </button>
                  {/* Apple Pay / Apple Sign In */}
                  <button style={{
                    display:'flex', alignItems:'center', justifyContent:'center', gap:12,
                    padding:'13px 20px', border:'1px solid var(--rule-strong)',
                    borderRadius:'var(--r-2)', background:'#000', cursor:'pointer',
                    fontFamily:'var(--f-ar)', fontSize:15, fontWeight:500, color:'#fff',
                    boxShadow:'var(--shadow-sm)'
                  }}>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="white">
                      <path d="M12.152 6.896c-.948 0-2.415-1.078-3.96-1.04-2.04.027-3.91 1.183-4.961 3.014-2.117 3.675-.546 9.103 1.519 12.09 1.013 1.454 2.208 3.09 3.792 3.039 1.52-.065 2.09-.987 3.935-.987 1.831 0 2.35.987 3.96.948 1.637-.026 2.676-1.48 3.676-2.948 1.156-1.688 1.636-3.325 1.662-3.415-.039-.013-3.182-1.221-3.22-4.857-.026-3.04 2.48-4.494 2.597-4.559-1.429-2.09-3.623-2.324-4.39-2.376-2-.156-3.675 1.09-4.61 1.09zM15.53 3.83c.843-1.012 1.4-2.427 1.245-3.83-1.207.052-2.662.805-3.532 1.818-.78.896-1.454 2.338-1.273 3.714 1.338.104 2.715-.688 3.559-1.701z"/>
                    </svg>
                    المتابعة بحساب Apple
                  </button>
                </div>

                {/* Divider */}
                <div style={{display:'flex', alignItems:'center', gap:12, marginBottom:24}}>
                  <div style={{flex:1, height:1, background:'var(--rule)'}}/>
                  <span className="mono-up" style={{color:'var(--dim)', fontSize:10}}>أو</span>
                  <div style={{flex:1, height:1, background:'var(--rule)'}}/>
                </div>

                {/* Method toggle */}
                <div style={{display:'flex', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', overflow:'hidden', marginBottom:20}}>
                  {[['email','البريد الإلكتروني'],['phone','رقم الجوال']].map(([m,l],i)=>(
                    <button key={i} onClick={()=>setMethod(m)} style={{
                      flex:1, padding:'10px', fontFamily:'var(--f-ar)', fontSize:13, fontWeight:500,
                      background: method===m ? 'var(--indigo)' : 'transparent',
                      color: method===m ? '#fff' : 'var(--ink-3)',
                      border:'none', cursor:'pointer', transition:'background .15s'
                    }}>{l}</button>
                  ))}
                </div>

                {/* Form fields */}
                <div style={{display:'flex', flexDirection:'column', gap:14}}>
                  {mode === 'signup' && (
                    <label style={{display:'flex', flexDirection:'column', gap:6}}>
                      <span className="mono-up" style={{color:'var(--dim)', fontSize:9}}>الاسم الكامل</span>
                      <input placeholder="أحمد السبيعي" style={{
                        padding:'13px 16px', border:'1px solid var(--rule-strong)', borderRadius:'var(--r-2)',
                        background:'var(--surface)', fontFamily:'var(--f-ar)', fontSize:15, color:'var(--ink)',
                        outline:'none'
                      }}/>
                    </label>
                  )}

                  {method === 'email' ? (
                    <label style={{display:'flex', flexDirection:'column', gap:6}}>
                      <span className="mono-up" style={{color:'var(--dim)', fontSize:9}}>البريد الإلكتروني</span>
                      <input type="email" placeholder="ahmad@example.sa" style={{
                        padding:'13px 16px', border:'1px solid var(--rule-strong)', borderRadius:'var(--r-2)',
                        background:'var(--surface)', fontFamily:'var(--f-en)', fontSize:15, color:'var(--ink)',
                        outline:'none'
                      }}/>
                    </label>
                  ) : (
                    <label style={{display:'flex', flexDirection:'column', gap:6}}>
                      <span className="mono-up" style={{color:'var(--dim)', fontSize:9}}>رقم الجوال</span>
                      <div style={{display:'flex', border:'1px solid var(--rule-strong)', borderRadius:'var(--r-2)', overflow:'hidden', background:'var(--surface)'}}>
                        <div style={{
                          padding:'13px 14px', borderInlineEnd:'1px solid var(--rule)',
                          fontFamily:'var(--f-mono)', fontSize:14, color:'var(--ink-3)',
                          background:'var(--surface-2)', display:'flex', gap:8, alignItems:'center'
                        }}>
                          <span>🇸🇦</span><span>+966</span>
                        </div>
                        <input type="tel" placeholder="50 123 4567" style={{
                          flex:1, padding:'13px 16px', border:'none', background:'transparent',
                          fontFamily:'var(--f-en)', fontSize:15, color:'var(--ink)', outline:'none'
                        }}/>
                      </div>
                    </label>
                  )}

                  {method === 'email' && (
                    <label style={{display:'flex', flexDirection:'column', gap:6}}>
                      <div style={{display:'flex', justifyContent:'space-between'}}>
                        <span className="mono-up" style={{color:'var(--dim)', fontSize:9}}>كلمة المرور</span>
                        {mode==='login' && <a href="#" style={{fontSize:12, color:'var(--accent-deep)', fontWeight:600}}>نسيت كلمة المرور؟</a>}
                      </div>
                      <div style={{position:'relative'}}>
                        <input type="password" placeholder="••••••••" style={{
                          width:'100%', padding:'13px 16px', border:'1px solid var(--rule-strong)', borderRadius:'var(--r-2)',
                          background:'var(--surface)', fontFamily:'var(--f-en)', fontSize:15, color:'var(--ink)',
                          outline:'none'
                        }}/>
                        <button style={{
                          position:'absolute', insetInlineEnd:12, top:'50%', transform:'translateY(-50%)',
                          fontFamily:'var(--f-mono)', fontSize:11, color:'var(--dim)'
                        }}>إظهار</button>
                      </div>
                    </label>
                  )}

                  {mode === 'signup' && (
                    <div style={{display:'flex', flexDirection:'column', gap:10}}>
                      {/* Select all master checkbox */}
                      <label style={{
                        display:'flex', gap:10, alignItems:'flex-start', cursor:'pointer',
                        padding:'10px 14px', background:'var(--surface-2)',
                        border:'1px solid var(--rule-strong)', borderRadius:'var(--r-2)'
                      }}>
                        <div style={{width:18, height:18, border:'2px solid var(--indigo)', borderRadius:'var(--r-1)', marginTop:2, flexShrink:0, background:'var(--indigo)', display:'grid', placeItems:'center'}}>
                          <span style={{color:'#fff', fontSize:12, fontWeight:700}}>✓</span>
                        </div>
                        <span style={{fontSize:14, color:'var(--indigo)', fontWeight:700, lineHeight:1.5}}>
                          الموافقة على الكل
                          <span style={{display:'block', fontSize:12, fontWeight:400, color:'var(--ink-4)', marginTop:2}}>الشروط + SMS + النشرة + العروض</span>
                        </span>
                      </label>
                      <div style={{height:1, background:'var(--rule)', margin:'2px 0'}}/>
                      {/* Terms — required */}
                      <label style={{display:'flex', gap:10, alignItems:'flex-start', cursor:'pointer'}}>
                        <div style={{width:18, height:18, border:'1px solid var(--rule-strong)', borderRadius:'var(--r-1)', marginTop:2, flexShrink:0, background:'var(--accent)', display:'grid', placeItems:'center'}}>
                          <span style={{color:'var(--indigo)', fontSize:12, fontWeight:700}}>✓</span>
                        </div>
                        <span style={{fontSize:13, color:'var(--ink-3)', lineHeight:1.5}}>
                          <span style={{color:'var(--sale)', fontWeight:600}}>* </span>
                          أوافق على <a href="#" style={{color:'var(--accent-deep)', fontWeight:600}}>الشروط والأحكام</a> و<a href="#" style={{color:'var(--accent-deep)', fontWeight:600}}>سياسة الخصوصية</a>
                        </span>
                      </label>
                      {/* SMS — opt-in, pre-checked */}
                      <label style={{display:'flex', gap:10, alignItems:'flex-start', cursor:'pointer'}}>
                        <div style={{width:18, height:18, border:'1px solid var(--rule-strong)', borderRadius:'var(--r-1)', marginTop:2, flexShrink:0, background:'var(--accent)', display:'grid', placeItems:'center'}}>
                          <span style={{color:'var(--indigo)', fontSize:12, fontWeight:700}}>✓</span>
                        </div>
                        <span style={{fontSize:13, color:'var(--ink-3)', lineHeight:1.5}}>
                          أوافق على استقبال تنبيهات <strong>SMS وواتساب</strong> لتحديثات الطلبات والشحن
                        </span>
                      </label>
                      {/* Newsletter — opt-in, unchecked */}
                      <label style={{display:'flex', gap:10, alignItems:'flex-start', cursor:'pointer'}}>
                        <div style={{width:18, height:18, border:'1px solid var(--rule-strong)', borderRadius:'var(--r-1)', marginTop:2, flexShrink:0, background:'var(--accent)', display:'grid', placeItems:'center'}}>
                          <span style={{color:'var(--indigo)', fontSize:12, fontWeight:700}}>✓</span>
                        </div>
                        <span style={{fontSize:13, color:'var(--ink-3)', lineHeight:1.5}}>
                          أرغب في استقبال <strong>النشرة الإخبارية</strong> — أحدث المنتجات والتقنية
                          <span className="chip chip-accent" style={{marginInlineStart:6, fontSize:9, padding:'2px 6px'}}>موصى به</span>
                        </span>
                      </label>
                      {/* Promos — opt-in, unchecked */}
                      <label style={{display:'flex', gap:10, alignItems:'flex-start', cursor:'pointer'}}>
                        <div style={{width:18, height:18, border:'1px solid var(--rule-strong)', borderRadius:'var(--r-1)', marginTop:2, flexShrink:0, background:'var(--accent)', display:'grid', placeItems:'center'}}>
                          <span style={{color:'var(--indigo)', fontSize:12, fontWeight:700}}>✓</span>
                        </div>
                        <span style={{fontSize:13, color:'var(--ink-3)', lineHeight:1.5}}>
                          أرغب في استقبال <strong>عروض وخصومات حصرية</strong> عبر البريد وواتساب
                          <span className="chip" style={{marginInlineStart:6, fontSize:9, padding:'2px 6px', border:'1px dashed var(--rule-strong)'}}>اختياري</span>
                        </span>
                      </label>
                      <div style={{padding:'8px 12px', background:'var(--surface-2)', border:'1px solid var(--rule)', borderRadius:'var(--r-1)', display:'flex', gap:6, alignItems:'flex-start'}}>
                        <span style={{color:'var(--sale)', flexShrink:0}}>*</span>
                        <span className="mono" style={{fontSize:10, color:'var(--dim)', lineHeight:1.5}}>الموافقة على الشروط إلزامية. باقي الخيارات اختيارية ويمكن إلغاؤها في إعدادات الحساب.</span>
                      </div>
                    </div>
                  )}

                  <button className="btn" style={{width:'100%', justifyContent:'center', borderRadius:'var(--r-2)', padding:16, fontSize:16, marginTop:4}}>
                    {method === 'phone'
                      ? 'إرسال رمز التحقق'
                      : mode === 'signup' ? 'إنشاء الحساب' : 'تسجيل الدخول'}
                  </button>
                </div>

                {/* Toggle mode */}
                <div style={{textAlign:'center', marginTop:24, fontSize:14, color:'var(--dim)'}}>
                  {mode === 'login'
                    ? <>ليس لديك حساب؟ <button onClick={()=>setMode('signup')} style={{color:'var(--accent-deep)', fontWeight:700, fontFamily:'var(--f-ar)', fontSize:14}}>أنشئ حساباً مجانياً</button></>
                    : <>لديك حساب؟ <button onClick={()=>setMode('login')} style={{color:'var(--accent-deep)', fontWeight:700, fontFamily:'var(--f-ar)', fontSize:14}}>سجّل دخولك</button></>
                  }
                </div>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};
window.Auth = Auth;
