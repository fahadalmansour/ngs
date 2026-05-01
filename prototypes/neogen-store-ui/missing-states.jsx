// Missing states & flows — fills critical gaps

// ─── 1. PAYMENT STEP (Step 3 of checkout) ───
const PaymentStep = () => (
  <div dir="rtl" style={{background:'var(--bg)', minHeight:'100%', fontFamily:'var(--f-ar)'}}>
    <TopBar/>
    <header style={{borderBottom:'1px solid var(--rule)', background:'rgba(248,250,252,0.95)'}}>
      <div style={{maxWidth:1440, margin:'0 auto', padding:'16px 48px', display:'flex', justifyContent:'space-between', alignItems:'center'}}>
        <Logo size={32}/>
        {/* Stepper */}
        <div style={{display:'flex', gap:0, alignItems:'center'}}>
          {[['01','السلة',false],['02','الشحن',false],['03','الدفع',true]].map(([n,l,a],i)=>(
            <React.Fragment key={i}>
              <div style={{display:'flex', alignItems:'center', gap:8, opacity:a?1:0.4}}>
                <span style={{
                  width:28, height:28, border:'1px solid var(--ink)', display:'grid', placeItems:'center',
                  background:a?'var(--indigo)':'transparent', color:a?'#fff':'var(--ink)',
                  fontFamily:'var(--f-mono)', fontSize:11, borderRadius:'var(--r-1)'
                }}>{i<2?'✓':n}</span>
                <span style={{fontSize:13, fontWeight:500}}>{l}</span>
              </div>
              {i<2 && <span style={{width:24, height:1, background:'var(--rule)', margin:'0 12px'}}></span>}
            </React.Fragment>
          ))}
        </div>
        <div className="mono-up" style={{color:'var(--good)', fontSize:9}}>● اتصال آمن SSL</div>
      </div>
    </header>

    <div style={{maxWidth:1280, margin:'0 auto', padding:'48px 48px 96px', display:'grid', gridTemplateColumns:'1.5fr 1fr', gap:48}}>
      <div style={{display:'flex', flexDirection:'column', gap:20}}>
        {/* One-tap wallets */}
        <div style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', padding:24}}>
          <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:14}}>دفع سريع · EXPRESS PAY</div>
          <div style={{display:'grid', gridTemplateColumns:'1fr 1fr', gap:10}}>
            <button style={{
              padding:'14px 20px', background:'#000', color:'#fff', borderRadius:'var(--r-2)',
              display:'flex', alignItems:'center', justifyContent:'center', gap:10,
              fontFamily:'var(--f-wordmark)', fontSize:14, fontWeight:600, cursor:'pointer', border:'none'
            }}>
              <svg width="18" height="18" viewBox="0 0 24 24" fill="white"><path d="M12.152 6.896c-.948 0-2.415-1.078-3.96-1.04-2.04.027-3.91 1.183-4.961 3.014-2.117 3.675-.546 9.103 1.519 12.09 1.013 1.454 2.208 3.09 3.792 3.039 1.52-.065 2.09-.987 3.935-.987 1.831 0 2.35.987 3.96.948 1.637-.026 2.676-1.48 3.676-2.948 1.156-1.688 1.636-3.325 1.662-3.415-.039-.013-3.182-1.221-3.22-4.857-.026-3.04 2.48-4.494 2.597-4.559-1.429-2.09-3.623-2.324-4.39-2.376-2-.156-3.675 1.09-4.61 1.09z"/></svg>
              Apple Pay
            </button>
            <button style={{
              padding:'14px 20px', background:'#fff', borderRadius:'var(--r-2)',
              display:'flex', alignItems:'center', justifyContent:'center', gap:10,
              fontFamily:'var(--f-wordmark)', fontSize:14, fontWeight:600, cursor:'pointer',
              border:'1px solid var(--rule)', boxShadow:'var(--shadow-sm)'
            }}>
              <svg width="20" height="20" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
              </svg>
              Google Pay
            </button>
            <button style={{
              padding:'14px 20px', background:'#7B2D8B', color:'#fff', borderRadius:'var(--r-2)',
              display:'flex', alignItems:'center', justifyContent:'center', gap:10,
              fontFamily:'var(--f-wordmark)', fontSize:14, fontWeight:600, cursor:'pointer', border:'none'
            }}>💜 STC Pay</button>
            <button style={{
              padding:'14px 20px', background:'#1A2B4B', color:'#fff', borderRadius:'var(--r-2)',
              display:'flex', alignItems:'center', justifyContent:'center', gap:10,
              fontFamily:'var(--f-wordmark)', fontSize:14, fontWeight:600, cursor:'pointer', border:'none'
            }}>💳 Mada</button>
          </div>
        </div>

        {/* Divider */}
        <div style={{display:'flex', alignItems:'center', gap:12}}>
          <div style={{flex:1, height:1, background:'var(--rule)'}}/>
          <span className="mono-up" style={{color:'var(--dim)', fontSize:10}}>أو ادفع بالبطاقة</span>
          <div style={{flex:1, height:1, background:'var(--rule)'}}/>
        </div>

        {/* Card form */}
        <div style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', padding:28}}>
          <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:20}}>بيانات البطاقة · CARD DETAILS</div>
          <div style={{display:'flex', flexDirection:'column', gap:16}}>
            <label style={{display:'flex', flexDirection:'column', gap:6}}>
              <span className="mono-up" style={{color:'var(--dim)', fontSize:9}}>رقم البطاقة</span>
              <div style={{position:'relative', display:'flex', alignItems:'center', border:'1px solid var(--rule-strong)', borderRadius:'var(--r-2)', overflow:'hidden', background:'var(--surface)'}}>
                <input placeholder="1234  5678  9012  3456" style={{flex:1, padding:'13px 16px', border:'none', background:'transparent', fontFamily:'var(--f-mono)', fontSize:16, letterSpacing:'0.1em', color:'var(--ink)', outline:'none'}}/>
                <div style={{padding:'0 14px', display:'flex', gap:6, opacity:0.5}}>
                  {['💳','💳'].map((c,i)=><span key={i} style={{fontSize:16}}>{c}</span>)}
                </div>
              </div>
            </label>
            <div style={{display:'grid', gridTemplateColumns:'1fr 1fr', gap:14}}>
              <label style={{display:'flex', flexDirection:'column', gap:6}}>
                <span className="mono-up" style={{color:'var(--dim)', fontSize:9}}>تاريخ الانتهاء</span>
                <input placeholder="MM / YY" style={{padding:'13px 16px', border:'1px solid var(--rule-strong)', borderRadius:'var(--r-2)', background:'var(--surface)', fontFamily:'var(--f-mono)', fontSize:15, color:'var(--ink)', outline:'none'}}/>
              </label>
              <label style={{display:'flex', flexDirection:'column', gap:6}}>
                <span className="mono-up" style={{color:'var(--dim)', fontSize:9}}>CVV</span>
                <div style={{position:'relative'}}>
                  <input placeholder="•••" type="password" style={{width:'100%', padding:'13px 16px', border:'1px solid var(--rule-strong)', borderRadius:'var(--r-2)', background:'var(--surface)', fontFamily:'var(--f-mono)', fontSize:15, color:'var(--ink)', outline:'none'}}/>
                  <span style={{position:'absolute', insetInlineEnd:12, top:'50%', transform:'translateY(-50%)', fontSize:14, color:'var(--dim)'}}>?</span>
                </div>
              </label>
            </div>
            <label style={{display:'flex', flexDirection:'column', gap:6}}>
              <span className="mono-up" style={{color:'var(--dim)', fontSize:9}}>اسم صاحب البطاقة</span>
              <input placeholder="AHMED AL-SUBAIE" style={{padding:'13px 16px', border:'1px solid var(--rule-strong)', borderRadius:'var(--r-2)', background:'var(--surface)', fontFamily:'var(--f-mono)', fontSize:15, textTransform:'uppercase', color:'var(--ink)', outline:'none'}}/>
            </label>
            <label style={{display:'flex', gap:10, alignItems:'center', cursor:'pointer'}}>
              <div style={{width:18, height:18, border:'1px solid var(--rule-strong)', borderRadius:3, background:'var(--accent)', display:'grid', placeItems:'center'}}>
                <span style={{color:'var(--indigo)', fontSize:12, fontWeight:700}}>✓</span>
              </div>
              <span style={{fontSize:13, color:'var(--ink-3)'}}>حفظ البطاقة لعمليات الشراء القادمة</span>
            </label>
          </div>
        </div>

        {/* Installments */}
        <div style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', padding:20}}>
          <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:12}}>تقسيط بدون فوائد · BNPL</div>
          <div style={{display:'grid', gridTemplateColumns:'1fr 1fr 1fr', gap:8}}>
            {[['Tamara','4 دفعات','1,270 SAR / دفعة'],['Tabby','6 دفعات','847 SAR / دفعة'],['SplitIt','12 دفعة','423 SAR / دفعة']].map(([b,n,a],i)=>(
              <div key={i} style={{padding:'12px 14px', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', cursor:'pointer'}}>
                <div style={{fontFamily:'var(--f-wordmark)', fontWeight:700, fontSize:13, color:'var(--indigo)'}}>{b}</div>
                <div style={{fontSize:12, color:'var(--ink-3)', marginTop:4}}>{n}</div>
                <div className="mono" style={{fontSize:11, color:'var(--accent-deep)', marginTop:2}}>{a}</div>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Order summary */}
      <aside>
        <div style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', padding:24, position:'sticky', top:24}}>
          <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:16}}>ملخّص الطلب</div>
          {[['NG-ENT-003','UDM-Pro',2054],['NT-WAP-TPL-002','EAP650 × 2',1598],['NT-CBL-GEN-001','Cat6a 305m',740]].map(([sku,n,p],i)=>(
            <div key={i} style={{display:'flex', justifyContent:'space-between', padding:'10px 0', borderBottom:'1px dashed var(--rule)', fontSize:13}}>
              <div>
                <div style={{fontWeight:500, color:'var(--indigo)'}}>{n}</div>
                <div className="mono" style={{fontSize:9, color:'var(--dim)', marginTop:2}}>{sku}</div>
              </div>
              <div className="en" style={{fontWeight:500}}>{p.toLocaleString('en-US')} SAR</div>
            </div>
          ))}
          <div style={{display:'flex', flexDirection:'column', gap:8, padding:'14px 0', borderBottom:'1px solid var(--rule)'}}>
            {[['المجموع الفرعي','4,392'],['شحن Aramex','25'],['خصم كود NEOGEN10','−439'],['ضريبة 15%','597']].map(([l,v],i)=>(
              <div key={i} style={{display:'flex', justifyContent:'space-between', fontSize:13}}>
                <span style={{color: i===2?'var(--good)':'var(--ink-3)'}}>{l}</span>
                <span className="en" style={{fontWeight:500, color: i===2?'var(--good)':'var(--ink)'}}>{v} SAR</span>
              </div>
            ))}
          </div>
          <div style={{display:'flex', justifyContent:'space-between', padding:'14px 0', fontWeight:700, fontSize:16}}>
            <span>الإجمالي</span>
            <div className="price"><span className="price-now" style={{fontSize:22}}>4,575</span><span className="price-sar">SAR</span></div>
          </div>
          <button className="btn" style={{width:'100%', justifyContent:'center', padding:16, borderRadius:'var(--r-2)', fontSize:16, marginBottom:12}}>
            ادفع الآن · 4,575 SAR 🔒
          </button>
          <div style={{display:'flex', justifyContent:'center', gap:8, flexWrap:'wrap', opacity:0.6}}>
            {['Visa','Mastercard','Mada','Apple Pay','Google Pay','STC Pay','Tabby','Tamara'].map((p,i)=>(
              <span key={i} className="mono-up" style={{fontSize:8, padding:'3px 6px', border:'1px solid var(--rule)', borderRadius:2}}>{p}</span>
            ))}
          </div>
        </div>
      </aside>
    </div>
  </div>
);

// ─── 2. EMPTY / ERROR STATES ───
const EmptyStates = () => (
  <div dir="rtl" style={{background:'var(--bg)', minHeight:'100%', fontFamily:'var(--f-ar)'}}>
    <TopBar/><Header/>
    <div style={{maxWidth:1440, margin:'0 auto', padding:'64px 48px 96px'}}>
      <div className="section-mark" style={{marginBottom:32}}><span>00</span><span style={{color:'var(--ink)'}}>· حالات فارغة وأخطاء · EMPTY & ERROR STATES</span></div>
      <div style={{display:'grid', gridTemplateColumns:'1fr 1fr 1fr', gap:20}}>

        {/* Empty cart */}
        <div style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-3)', padding:'48px 32px', textAlign:'center', display:'flex', flexDirection:'column', alignItems:'center', gap:16}}>
          <div style={{fontSize:56}}>🛒</div>
          <h3 style={{margin:0, fontSize:22, fontWeight:700, color:'var(--indigo)'}}>سلتك فارغة</h3>
          <p style={{margin:0, fontSize:14, color:'var(--dim)', lineHeight:1.6, maxWidth:240}}>لم تُضف أي منتجات بعد. تصفّح المتجر واختر ما يناسبك.</p>
          <button className="btn" style={{borderRadius:'var(--r-2)'}}>تصفّح المتجر →</button>
        </div>

        {/* No search results */}
        <div style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-3)', padding:'48px 32px', textAlign:'center', display:'flex', flexDirection:'column', alignItems:'center', gap:16}}>
          <div style={{fontSize:56}}>🔍</div>
          <h3 style={{margin:0, fontSize:22, fontWeight:700, color:'var(--indigo)'}}>لا نتائج لـ "Unifi Dream"</h3>
          <p style={{margin:0, fontSize:14, color:'var(--dim)', lineHeight:1.6, maxWidth:240}}>جرّب كلمات مختلفة أو تصفّح الفئات مباشرةً.</p>
          <div style={{display:'flex', gap:8, flexWrap:'wrap', justifyContent:'center'}}>
            {['الشبكات','Ubiquiti','هوم لاب'].map((s,i)=>(
              <button key={i} className="chip chip-accent" style={{padding:'6px 12px', cursor:'pointer'}}>{s}</button>
            ))}
          </div>
        </div>

        {/* Out of stock */}
        <div style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-3)', padding:'48px 32px', textAlign:'center', display:'flex', flexDirection:'column', alignItems:'center', gap:16}}>
          <div style={{fontSize:56}}>📦</div>
          <span className="chip chip-sale">نفد المخزون</span>
          <h3 style={{margin:0, fontSize:22, fontWeight:700, color:'var(--indigo)'}}>نفد هذا المنتج</h3>
          <p style={{margin:0, fontSize:14, color:'var(--dim)', lineHeight:1.6, maxWidth:240}}>سجّل بريدك وسنُخطرك فور توفّره.</p>
          <div style={{display:'flex', gap:0, width:'100%', maxWidth:280}}>
            <input placeholder="بريدك الإلكتروني" style={{flex:1, padding:'11px 14px', border:'1px solid var(--rule-strong)', borderRadius:'var(--r-2) 0 0 var(--r-2)', background:'var(--bg)', fontFamily:'var(--f-ar)', fontSize:13, outline:'none'}}/>
            <button className="btn btn-sm" style={{borderRadius:'0 var(--r-2) var(--r-2) 0', padding:'11px 14px'}}>أخبرني</button>
          </div>
        </div>

        {/* Payment failed */}
        <div style={{background:'var(--surface)', border:'1px solid var(--sale)', borderRadius:'var(--r-3)', padding:'48px 32px', textAlign:'center', display:'flex', flexDirection:'column', alignItems:'center', gap:16}}>
          <div style={{fontSize:56}}>❌</div>
          <h3 style={{margin:0, fontSize:22, fontWeight:700, color:'var(--sale)'}}>فشلت عملية الدفع</h3>
          <p style={{margin:0, fontSize:14, color:'var(--dim)', lineHeight:1.6, maxWidth:240}}>تحقق من بيانات بطاقتك أو جرّب طريقة دفع أخرى.</p>
          <div style={{background:'var(--sale-soft)', border:'1px solid var(--sale)', borderRadius:'var(--r-1)', padding:'10px 14px', width:'100%', textAlign:'start'}}>
            <div className="mono" style={{fontSize:11, color:'var(--sale)'}}>خطأ: CARD_DECLINED · رمز: 0051</div>
          </div>
          <button className="btn" style={{borderRadius:'var(--r-2)'}}>إعادة المحاولة</button>
        </div>

        {/* 404 */}
        <div style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-3)', padding:'48px 32px', textAlign:'center', display:'flex', flexDirection:'column', alignItems:'center', gap:16}}>
          <div style={{fontFamily:'var(--f-wordmark)', fontSize:72, fontWeight:700, color:'var(--indigo)', lineHeight:1, opacity:0.15}}>404</div>
          <h3 style={{margin:'-24px 0 0', fontSize:22, fontWeight:700, color:'var(--indigo)'}}>الصفحة غير موجودة</h3>
          <p style={{margin:0, fontSize:14, color:'var(--dim)', lineHeight:1.6, maxWidth:240}}>الرابط قديم أو الصفحة نُقلت. ابدأ من الرئيسية.</p>
          <div style={{display:'flex', gap:8}}>
            <button className="btn btn-sm" style={{borderRadius:'var(--r-2)'}}>الرئيسية</button>
            <button className="btn btn-ghost btn-sm" style={{borderRadius:'var(--r-2)'}}>تواصل معنا</button>
          </div>
        </div>

        {/* Order failed */}
        <div style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-3)', padding:'48px 32px', textAlign:'center', display:'flex', flexDirection:'column', alignItems:'center', gap:16}}>
          <div style={{fontSize:56}}>⚠️</div>
          <h3 style={{margin:0, fontSize:22, fontWeight:700, color:'var(--warn)'}}>خطأ في إتمام الطلب</h3>
          <p style={{margin:0, fontSize:14, color:'var(--dim)', lineHeight:1.6, maxWidth:240}}>حدث خطأ غير متوقع. طلبك لم يُسجَّل — لم تُخصم أي مبالغ.</p>
          <button className="btn" style={{borderRadius:'var(--r-2)', background:'var(--warn)', color:'#fff', boxShadow:'none'}}>إعادة المحاولة</button>
          <a href="#" style={{fontSize:13, color:'var(--accent-deep)', fontWeight:600}}>التواصل مع الدعم →</a>
        </div>
      </div>
    </div>
    <Footer/>
  </div>
);

// ─── 3. FORGOT PASSWORD ───
const ForgotPassword = () => {
  const [sent, setSent] = React.useState(false);
  return (
    <div dir="rtl" style={{background:'var(--bg)', minHeight:'100%', fontFamily:'var(--f-ar)', display:'flex', flexDirection:'column'}}>
      <TopBar/>
      <div style={{flex:1, display:'grid', placeItems:'center', padding:48}}>
        <div style={{
          background:'var(--surface)', border:'1px solid var(--rule)',
          borderRadius:'var(--r-3)', padding:'48px 40px', maxWidth:480, width:'100%',
          boxShadow:'var(--shadow-lg)', textAlign:'center'
        }}>
          {sent ? (
            <>
              <div style={{fontSize:64, marginBottom:20}}>📬</div>
              <h2 style={{margin:'0 0 12px', fontSize:26, fontWeight:700, color:'var(--indigo)'}}>تحقق من بريدك</h2>
              <p style={{margin:'0 0 28px', fontSize:15, color:'var(--dim)', lineHeight:1.65}}>
                أرسلنا رابط إعادة تعيين كلمة المرور إلى <strong>ahmad@example.sa</strong>. تحقق من مجلد البريد المزعج إن لم يصل.
              </p>
              <div className="chip chip-accent" style={{margin:'0 auto 24px', padding:'8px 16px'}}>● الرابط صالح لمدة 30 دقيقة</div>
              <button className="btn btn-ghost btn-sm" onClick={()=>setSent(false)} style={{borderRadius:'var(--r-2)'}}>إعادة الإرسال</button>
            </>
          ) : (
            <>
              <div style={{fontSize:64, marginBottom:20}}>🔐</div>
              <h2 style={{margin:'0 0 12px', fontSize:26, fontWeight:700, color:'var(--indigo)'}}>نسيت كلمة المرور؟</h2>
              <p style={{margin:'0 0 28px', fontSize:15, color:'var(--dim)', lineHeight:1.65}}>
                أدخل بريدك الإلكتروني أو رقم جوالك وسنرسل لك رابط إعادة التعيين.
              </p>
              <div style={{display:'flex', flexDirection:'column', gap:14, textAlign:'start', marginBottom:20}}>
                <label style={{display:'flex', flexDirection:'column', gap:6}}>
                  <span className="mono-up" style={{color:'var(--dim)', fontSize:9}}>البريد الإلكتروني أو رقم الجوال</span>
                  <input placeholder="ahmad@example.sa أو 05xxxxxxxx" style={{
                    padding:'13px 16px', border:'1px solid var(--rule-strong)', borderRadius:'var(--r-2)',
                    background:'var(--bg)', fontFamily:'var(--f-ar)', fontSize:14, color:'var(--ink)', outline:'none'
                  }}/>
                </label>
              </div>
              <button className="btn" onClick={()=>setSent(true)} style={{width:'100%', justifyContent:'center', borderRadius:'var(--r-2)', padding:14, marginBottom:16}}>
                إرسال رابط إعادة التعيين
              </button>
              <a href="#" style={{fontSize:13, color:'var(--accent-deep)', fontWeight:600}}>← العودة لتسجيل الدخول</a>
            </>
          )}
        </div>
      </div>
    </div>
  );
};

Object.assign(window, {PaymentStep, EmptyStates, ForgotPassword});
