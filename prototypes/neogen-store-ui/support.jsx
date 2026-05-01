// Support / Messaging page — per purchase item
const Support = () => {
  const [activeTicket, setActiveTicket] = React.useState(0);
  const [msg, setMsg] = React.useState('');

  const tickets = [
    {
      id:'TKT-2026-0441', order:'NG-2026-04721', status:'مفتوح', statusColor:'var(--accent-deep)',
      subject:'سؤال عن إعداد UDM-Pro مع UniFi', created:'2 مايو 2026', sku:'NG-ENT-003',
      product:'Ubiquiti UniFi Dream Machine Pro', ph:'UDM-Pro',
      messages:[
        {from:'user', name:'أحمد السبيعي', time:'2 مايو · 4:15م', text:'مرحباً، وصل الجهاز بحمد الله. لكن عندي سؤال — كيف أربط نقطة وصول U6 Pro مع UDM-Pro؟ هل تحتاج إعداد خاص؟'},
        {from:'support', name:'فريق نيوجن', time:'2 مايو · 5:02م', avatar:'NG', text:'أهلاً أحمد! يسعدنا مساعدتك. نعم، العملية بسيطة جداً:\n1. وصّل U6 Pro بكابل إيثرنت على أحد منافذ UDM-Pro\n2. افتح تطبيق UniFi Network\n3. الجهاز سيظهر تلقائياً تحت "Pending Devices"\n4. اضغط Adopt وانتظر دقيقتين\n\nهل تريد فيديو توضيحي؟'},
        {from:'user', name:'أحمد السبيعي', time:'2 مايو · 5:30م', text:'ممتاز! جربت الخطوات وشغلت. شكراً جزيلاً!'},
        {from:'support', name:'فريق نيوجن', time:'2 مايو · 5:45م', avatar:'NG', text:'عظيم! إذا واجهتك أي مشكلة أخرى نحن هنا. هل تريد إغلاق التذكرة؟'},
      ]
    },
    {
      id:'TKT-2026-0389', order:'NG-2026-03891', status:'مغلق', statusColor:'var(--good)',
      subject:'طلب فاتورة ضريبية رسمية', created:'16 أبريل 2026', sku:'SH-HUB-HASS-001',
      product:'Home Assistant Green', ph:'HA Green',
      messages:[
        {from:'user', name:'أحمد السبيعي', time:'16 أبريل · 10:00ص', text:'مرحباً، أحتاج فاتورة ضريبية رسمية للمنتج بالاسم التجاري للشركة.'},
        {from:'support', name:'فريق نيوجن', time:'16 أبريل · 11:30ص', avatar:'NG', text:'أهلاً! سنرسل الفاتورة على بريدك الإلكتروني خلال ساعة. ما هو الاسم التجاري؟'},
        {from:'user', name:'أحمد السبيعي', time:'16 أبريل · 11:45ص', text:'شركة تقنية الغد للتجارة — السجل التجاري 1234567890'},
        {from:'support', name:'فريق نيوجن', time:'16 أبريل · 1:00م', avatar:'NG', text:'تم إرسال الفاتورة الضريبية الرسمية على بريدك. يمكنك أيضاً تحميلها من حسابك.'},
      ]
    },
  ];

  const t = tickets[activeTicket];

  return (
    <div dir="rtl" style={{background:'var(--bg)', minHeight:'100%', fontFamily:'var(--f-ar)'}}>
      <TopBar/>
      <Header/>

      <div style={{maxWidth:1440, margin:'0 auto', padding:'48px 48px 96px', display:'grid', gridTemplateColumns:'320px 1fr', gap:24, alignItems:'start'}}>

        {/* Tickets sidebar */}
        <aside>
          <div style={{display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:14}}>
            <div className="section-mark" style={{flex:1}}><span>00</span><span style={{color:'var(--ink)'}}>· تذاكر الدعم</span></div>
          </div>
          <button className="btn" style={{width:'100%', justifyContent:'center', borderRadius:'var(--r-2)', marginBottom:14}}>+ فتح تذكرة جديدة</button>

          <div style={{display:'flex', flexDirection:'column', gap:8}}>
            {tickets.map((tk,i)=>(
              <button key={i} onClick={()=>setActiveTicket(i)} style={{
                padding:16, border:`1px solid ${activeTicket===i?'var(--accent)':'var(--rule)'}`,
                borderRadius:'var(--r-2)', background: activeTicket===i?'var(--accent-wash)':'var(--surface)',
                cursor:'pointer', textAlign:'start', display:'flex', flexDirection:'column', gap:8,
                boxShadow:'var(--shadow-sm)'
              }}>
                <div style={{display:'flex', justifyContent:'space-between', alignItems:'flex-start', gap:8}}>
                  <div className="mono" style={{fontSize:10, color:'var(--dim)'}}>{tk.id}</div>
                  <span style={{fontFamily:'var(--f-mono)', fontSize:9, textTransform:'uppercase', letterSpacing:'0.06em', color:tk.statusColor}}>● {tk.status}</span>
                </div>
                <div style={{fontWeight:600, fontSize:13, color:'var(--indigo)', lineHeight:1.3}}>{tk.subject}</div>
                <div style={{display:'flex', gap:8, alignItems:'center'}}>
                  <div className="ph" style={{width:28, height:28, flexShrink:0, borderRadius:'var(--r-1)'}}><span className="ph-label" style={{fontSize:6}}>{tk.ph}</span></div>
                  <span className="mono" style={{fontSize:10, color:'var(--dim)'}}>{tk.sku}</span>
                </div>
                <div className="mono" style={{fontSize:10, color:'var(--dim)'}}>{tk.created}</div>
              </button>
            ))}
          </div>

          {/* Help topics */}
          <div style={{marginTop:20, background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', overflow:'hidden'}}>
            <div style={{padding:'12px 16px', borderBottom:'1px solid var(--rule)', background:'var(--surface-2)'}}>
              <span className="mono-up" style={{color:'var(--ink-4)'}}>مواضيع شائعة</span>
            </div>
            {[
              ['📦','تتبع الشحنة'],
              ['🛡️','طلب ضمان / صيانة'],
              ['🔄','إرجاع أو استبدال'],
              ['🧾','فاتورة ضريبية'],
              ['🔑','كود بطاقة رقمية'],
              ['⚙️','إعداد المنتج'],
            ].map(([icon,label],i)=>(
              <button key={i} style={{
                width:'100%', padding:'11px 16px', display:'flex', gap:10, alignItems:'center',
                borderBottom: i<5?'1px solid var(--rule)':'none',
                background:'transparent', cursor:'pointer', textAlign:'start',
                fontFamily:'var(--f-ar)', fontSize:13, color:'var(--ink-3)'
              }}>
                <span style={{fontSize:16}}>{icon}</span>{label}
                <span style={{marginInlineStart:'auto', color:'var(--dim)', fontSize:12}}>←</span>
              </button>
            ))}
          </div>
        </aside>

        {/* Chat panel */}
        <main style={{display:'flex', flexDirection:'column', gap:0}}>
          {/* Ticket header */}
          <div style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-2) var(--r-2) 0 0', padding:'16px 24px', display:'grid', gridTemplateColumns:'56px 1fr auto', gap:16, alignItems:'center'}}>
            <div className="ph" style={{height:56, borderRadius:'var(--r-1)'}}><span className="ph-label" style={{fontSize:7}}>{t.ph}</span></div>
            <div>
              <div style={{fontWeight:700, fontSize:15, color:'var(--indigo)'}}>{t.subject}</div>
              <div style={{display:'flex', gap:12, marginTop:4, flexWrap:'wrap'}}>
                <span className="mono" style={{fontSize:11, color:'var(--dim)'}}>{t.id}</span>
                <span className="mono" style={{fontSize:11, color:'var(--dim)'}}>· طلب {t.order}</span>
                <span className="mono" style={{fontSize:11, color:'var(--dim)'}}>· {t.sku}</span>
              </div>
            </div>
            <div style={{display:'flex', gap:8, alignItems:'center'}}>
              <span style={{fontFamily:'var(--f-mono)', fontSize:11, textTransform:'uppercase', color:t.statusColor}}>● {t.status}</span>
              {t.status === 'مفتوح' && (
                <button className="btn btn-ghost btn-sm" style={{borderRadius:'var(--r-1)', fontSize:11}}>إغلاق</button>
              )}
            </div>
          </div>

          {/* Messages */}
          <div style={{
            background:'var(--surface)', borderInline:'1px solid var(--rule)',
            padding:'24px', display:'flex', flexDirection:'column', gap:16,
            minHeight:400, maxHeight:500, overflowY:'auto'
          }}>
            {t.messages.map((m,i)=>{
              const isUser = m.from === 'user';
              return (
                <div key={i} style={{display:'flex', gap:12, flexDirection: isUser?'row-reverse':'row', alignItems:'flex-start'}}>
                  {/* Avatar */}
                  <div style={{
                    width:36, height:36, borderRadius:'50%', flexShrink:0,
                    background: isUser ? `linear-gradient(135deg, var(--indigo), var(--accent))` : 'var(--indigo-deep)',
                    display:'grid', placeItems:'center',
                    fontFamily:'var(--f-wordmark)', fontWeight:700, fontSize:13, color:'#fff'
                  }}>{isUser ? 'أح' : 'NG'}</div>
                  <div style={{maxWidth:'72%'}}>
                    <div style={{display:'flex', gap:8, marginBottom:6, flexDirection: isUser?'row-reverse':'row', alignItems:'baseline'}}>
                      <span style={{fontWeight:600, fontSize:13, color:'var(--indigo)'}}>{m.name}</span>
                      <span className="mono" style={{fontSize:10, color:'var(--dim)'}}>{m.time}</span>
                    </div>
                    <div style={{
                      padding:'12px 16px', borderRadius: isUser ? 'var(--r-2) 4px var(--r-2) var(--r-2)' : '4px var(--r-2) var(--r-2) var(--r-2)',
                      background: isUser ? 'var(--indigo)' : 'var(--surface-2)',
                      color: isUser ? '#fff' : 'var(--ink-2)',
                      fontSize:14, lineHeight:1.65, whiteSpace:'pre-line',
                      border: isUser ? 'none' : '1px solid var(--rule)'
                    }}>{m.text}</div>
                  </div>
                </div>
              );
            })}
            {/* Typing indicator */}
            {t.status === 'مفتوح' && (
              <div style={{display:'flex', gap:12, alignItems:'center'}}>
                <div style={{width:36, height:36, borderRadius:'50%', background:'var(--indigo-deep)', display:'grid', placeItems:'center', fontFamily:'var(--f-wordmark)', fontWeight:700, fontSize:13, color:'#fff'}}>NG</div>
                <div style={{padding:'10px 16px', background:'var(--surface-2)', border:'1px solid var(--rule)', borderRadius:'4px var(--r-2) var(--r-2) var(--r-2)', display:'flex', gap:4, alignItems:'center'}}>
                  {[0,1,2].map(i=><div key={i} style={{width:6, height:6, borderRadius:'50%', background:'var(--dim)'}}/>)}
                </div>
              </div>
            )}
          </div>

          {/* Message input */}
          <div style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'0 0 var(--r-2) var(--r-2)', borderTop:'none', padding:16}}>
            {/* Attachments */}
            <div style={{display:'flex', gap:8, marginBottom:10}}>
              {['📎 مرفق','📸 صورة','📹 فيديو','🔢 رقم التتبع'].map((a,i)=>(
                <button key={i} className="chip" style={{padding:'5px 10px', fontSize:11, cursor:'pointer'}}>{a}</button>
              ))}
            </div>
            <div style={{display:'flex', gap:10}}>
              <textarea
                value={msg}
                onChange={e=>setMsg(e.target.value)}
                placeholder="اكتب رسالتك هنا..."
                rows={3}
                style={{
                  flex:1, padding:'12px 16px', border:'1px solid var(--rule-strong)',
                  borderRadius:'var(--r-2)', background:'var(--bg)',
                  fontFamily:'var(--f-ar)', fontSize:14, color:'var(--ink)',
                  resize:'none', outline:'none', lineHeight:1.5
                }}
              />
              <div style={{display:'flex', flexDirection:'column', gap:8}}>
                <button className="btn" style={{borderRadius:'var(--r-2)', padding:'12px 20px'}}>إرسال</button>
                <button className="btn btn-ghost btn-sm" style={{borderRadius:'var(--r-2)', fontSize:12}}>واتساب</button>
              </div>
            </div>
            <div style={{marginTop:10, display:'flex', justifyContent:'space-between'}}>
              <span className="mono-up" style={{color:'var(--dim)', fontSize:9}}>رد خلال · أقل من ساعة عمل</span>
              <span className="mono-up" style={{color:'var(--good)', fontSize:9}}>● الدعم متاح 9ص–9م</span>
            </div>
          </div>
        </main>
      </div>
      <Footer/>
    </div>
  );
};
window.Support = Support;
