// Notifications Center
const Notifications = () => {
  const [filter, setFilter] = React.useState('all');
  const notifs = [
    {type:'shipping', icon:'📦', title:'شحنتك في الطريق', body:'طلب NG-2026-04721 تم تسليمه لـ Aramex. رمز التتبع: 1234567890AR', time:'منذ ٣٠ دقيقة', read:false, tag:'شحن'},
    {type:'promo', icon:'🏷️', title:'عرض خاص لك · ٢٠% خصم', body:'كود NEOGEN20 صالح لمدة 24 ساعة على فئة الشبكات. انتهز الفرصة!', time:'منذ ساعتين', read:false, tag:'عروض'},
    {type:'restock', icon:'🟢', title:'عاد للمخزون!', body:'Ubiquiti UniFi U6 Pro الذي أضفته للمفضلة أصبح متاحاً. المخزون محدود.', time:'منذ ٣ ساعات', read:false, tag:'مخزون'},
    {type:'order', icon:'✅', title:'تم تأكيد طلبك', body:'طلب NG-2026-04721 بقيمة 5,080 SAR تم تأكيده. سيتم الشحن خلال يوم عمل.', time:'أمس · 3:42م', read:true, tag:'طلبات'},
    {type:'support', icon:'💬', title:'رد جديد من فريق الدعم', body:'فريق نيوجن رد على تذكرتك TKT-2026-0441 حول إعداد UDM-Pro.', time:'أمس · 5:02م', read:true, tag:'دعم'},
    {type:'warranty', icon:'🛡️', title:'تذكير: ضمان ينتهي قريباً', body:'ضمان Elgato Stream Deck MK.2 ينتهي خلال 30 يوماً. سجّل الضمان الموسّع الآن.', time:'منذ يومين', read:true, tag:'ضمان'},
    {type:'promo', icon:'🎁', title:'بطاقة مجانية مع كل طلب فوق 2000 SAR', body:'اطلب الآن واحصل على بطاقة Google Play بقيمة 50 SAR مجاناً.', time:'منذ ٣ أيام', read:true, tag:'عروض'},
    {type:'order', icon:'⭐', title:'قيّم طلبك الأخير', body:'كيف كانت تجربتك مع طلب NG-2026-03891؟ رأيك يساعدنا كثيراً.', time:'منذ أسبوع', read:true, tag:'طلبات'},
  ];

  const filters = ['all','شحن','طلبات','عروض','دعم','ضمان','مخزون'];
  const unread = notifs.filter(n=>!n.read).length;
  const shown = filter==='all' ? notifs : notifs.filter(n=>n.tag===filter);

  return (
    <div dir="rtl" style={{background:'var(--bg)', minHeight:'100%', fontFamily:'var(--f-ar)'}}>
      <TopBar/><Header/>
      <div style={{maxWidth:900, margin:'0 auto', padding:'48px 48px 96px'}}>
        {/* Header */}
        <div style={{display:'flex', justifyContent:'space-between', alignItems:'flex-end', marginBottom:28}}>
          <div>
            <div className="section-mark" style={{marginBottom:12}}><span>00</span><span style={{color:'var(--ink)'}}>· الإشعارات · NOTIFICATIONS</span></div>
            <h1 className="t-h1" style={{margin:0, color:'var(--indigo)', display:'flex', gap:12, alignItems:'center'}}>
              الإشعارات
              {unread > 0 && <span style={{background:'var(--sale)', color:'#fff', borderRadius:'var(--r-pill)', padding:'2px 10px', fontFamily:'var(--f-wordmark)', fontSize:16, fontWeight:700}}>{unread}</span>}
            </h1>
          </div>
          <div style={{display:'flex', gap:8}}>
            <button className="btn btn-ghost btn-sm" style={{borderRadius:'var(--r-2)'}}>تحديد الكل كمقروء</button>
            <button className="btn btn-ghost btn-sm" style={{borderRadius:'var(--r-2)', color:'var(--sale)'}}>حذف المقروءة</button>
          </div>
        </div>

        {/* Filter tabs */}
        <div style={{display:'flex', gap:8, marginBottom:24, flexWrap:'wrap'}}>
          {filters.map((f,i)=>(
            <button key={i} onClick={()=>setFilter(f)} className={filter===f?'chip chip-solid':'chip'} style={{padding:'7px 14px', fontSize:11, cursor:'pointer'}}>
              {f==='all'?`الكل (${notifs.length})`:f}
            </button>
          ))}
        </div>

        {/* Notifications list */}
        <div style={{display:'flex', flexDirection:'column', gap:8}}>
          {shown.map((n,i)=>(
            <div key={i} style={{
              background: n.read?'var(--surface)':'var(--surface)',
              border:`1px solid ${n.read?'var(--rule)':'var(--accent)'}`,
              borderRadius:'var(--r-2)', padding:'16px 20px',
              display:'grid', gridTemplateColumns:'44px 1fr auto', gap:16, alignItems:'flex-start',
              boxShadow: n.read?'var(--shadow-sm)':'0 0 0 3px var(--accent-wash)',
              position:'relative'
            }}>
              {!n.read && <div style={{position:'absolute', insetInlineStart:0, top:0, bottom:0, width:3, background:'var(--accent)', borderRadius:'var(--r-2) 0 0 var(--r-2)'}}/>}
              {/* Icon */}
              <div style={{
                width:44, height:44, borderRadius:'50%', background:'var(--surface-2)',
                display:'grid', placeItems:'center', fontSize:20, border:'1px solid var(--rule)'
              }}>{n.icon}</div>
              {/* Content */}
              <div>
                <div style={{display:'flex', gap:8, alignItems:'center', marginBottom:4}}>
                  <span style={{fontWeight:700, fontSize:15, color:'var(--indigo)'}}>{n.title}</span>
                  {!n.read && <span className="chip chip-sky" style={{fontSize:8, padding:'2px 6px'}}>جديد</span>}
                  <span className="chip" style={{fontSize:8, padding:'2px 6px'}}>{n.tag}</span>
                </div>
                <p style={{margin:0, fontSize:14, color:'var(--ink-3)', lineHeight:1.55}}>{n.body}</p>
                <div className="mono" style={{fontSize:11, color:'var(--dim)', marginTop:6}}>{n.time}</div>
              </div>
              {/* Actions */}
              <div style={{display:'flex', flexDirection:'column', gap:6, alignItems:'flex-end'}}>
                {n.type==='shipping' && <button className="btn btn-sm" style={{borderRadius:'var(--r-1)', fontSize:11, whiteSpace:'nowrap'}}>تتبع الشحنة</button>}
                {n.type==='restock' && <button className="btn btn-sm" style={{borderRadius:'var(--r-1)', fontSize:11, whiteSpace:'nowrap'}}>أضف للسلة</button>}
                {n.type==='promo' && <button className="btn btn-sm" style={{borderRadius:'var(--r-1)', fontSize:11, whiteSpace:'nowrap'}}>تسوّق الآن</button>}
                {n.type==='support' && <button className="btn btn-sm" style={{borderRadius:'var(--r-1)', fontSize:11, whiteSpace:'nowrap'}}>عرض الرد</button>}
                {n.type==='order' && <button className="btn btn-ghost btn-sm" style={{borderRadius:'var(--r-1)', fontSize:11, whiteSpace:'nowrap'}}>قيّم الآن</button>}
                <button style={{background:'none', border:'none', fontFamily:'var(--f-mono)', fontSize:10, color:'var(--dim)', cursor:'pointer'}}>تجاهل</button>
              </div>
            </div>
          ))}
        </div>

        {/* Notification settings */}
        <div style={{marginTop:32, background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', padding:24}}>
          <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:16}}>إعدادات الإشعارات · PREFERENCES</div>
          <div style={{display:'grid', gridTemplateColumns:'1fr 1fr', gap:0, border:'1px solid var(--rule)', borderRadius:'var(--r-1)', overflow:'hidden'}}>
            {[
              {label:'تحديثات الشحن', ch:'واتساب + SMS', on:true},
              {label:'تأكيد الطلبات', ch:'بريد إلكتروني', on:true},
              {label:'عروض وتخفيضات', ch:'إشعار تطبيق', on:false},
              {label:'توفّر المخزون', ch:'بريد إلكتروني', on:true},
              {label:'تذكيرات الضمان', ch:'SMS', on:true},
              {label:'ردود فريق الدعم', ch:'واتساب + إشعار', on:true},
            ].map((pref,i)=>(
              <div key={i} style={{
                display:'flex', justifyContent:'space-between', alignItems:'center',
                padding:'12px 16px', borderBottom:i<4?'1px solid var(--rule)':'none',
                borderInlineEnd:(i%2===0)?'1px solid var(--rule)':'none'
              }}>
                <div>
                  <div style={{fontWeight:500, fontSize:13, color:'var(--indigo)'}}>{pref.label}</div>
                  <div className="mono" style={{fontSize:10, color:'var(--dim)', marginTop:2}}>{pref.ch}</div>
                </div>
                <div style={{
                  width:40, height:22, borderRadius:11, cursor:'pointer',
                  background:pref.on?'var(--accent)':'var(--rule-strong)',
                  position:'relative', transition:'background .2s'
                }}>
                  <div style={{
                    width:18, height:18, borderRadius:'50%', background:'#fff',
                    position:'absolute', top:2, transition:'insetInlineStart .2s',
                    insetInlineStart:pref.on?20:2, boxShadow:'0 1px 3px rgba(0,0,0,0.2)'
                  }}/>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
      <Footer/>
    </div>
  );
};
window.Notifications = Notifications;
