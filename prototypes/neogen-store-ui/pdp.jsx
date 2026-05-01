// Product Detail Page — with Works Best With + Add-ons & Replacements
const PDP = () => {
  const [activeTab, setActiveTab] = React.useState(0);

  const worksWith = [
    {sku:'NT-WAP-UBQ-001', ar:'Ubiquiti UniFi U6 Pro', en:'WiFi 6 AP · 4×4 MU-MIMO', price:725, why:'نقطة وصول WiFi 6 مُدارة مباشرة من UDM-Pro', ph:'U6 Pro'},
    {sku:'NT-POE-UBQ-001', ar:'Ubiquiti USW-Lite-8-PoE', en:'8-Port PoE Switch', price:849, why:'سويتش PoE موسّع للأجهزة بعيدة عن الراوتر', ph:'USW-Lite'},
    {sku:'NG-SEC-002', ar:'UniFi G4 Pro Camera', en:'4K · PoE · NVR-ready', price:2008, why:'يتكامل مع NVR المدمج في UDM-Pro مباشرة', ph:'G4 Pro'},
    {sku:'NT-SFP-FSC-001', ar:'10G SFP+ Transceiver', en:'FS.com · 10GBASE-SR', price:129, why:'لتفعيل منفذ الـ 10G SFP+ الموجود في الجهاز', ph:'SFP+'},
  ];

  const replacements = [
    {sku:'NT-CBL-FSC-001', ar:'كابل DAC Twinax 10G', en:'SFP+ DAC 1m · Direct Attach', price:80, type:'upgrade', ph:'DAC cable'},
    {sku:'NG-ACC-HDD-001', ar:'Western Digital Red Plus 4TB', en:'NAS HDD · SATA 3.5" · WD', price:699, type:'consumable', ph:'HDD 3.5"'},
    {sku:'NG-CBL-GEN-001', ar:'كابل Cat6a مصفّح 305م', en:'Cat6a Shielded Bulk · 305m', price:740, type:'consumable', ph:'cat6a coil'},
    {sku:'NT-WAL-001', ar:'وحدة طاقة احتياطية C13', en:'IEC C13 Power Cable · 2m', price:49, type:'spare', ph:'power cable'},
    {sku:'NG-RACK-001', ar:'أقواس Rack 1U', en:'Rack Mount Kit · 1U universal', price:89, type:'spare', ph:'rack ears'},
    {sku:'NG-SEC-001', ar:'UniFi NVR HDD 8TB', en:'Seagate IronWolf 8TB NAS', price:1199, type:'upgrade', ph:'HDD 8TB'},
  ];

  const typeLabel = {upgrade:'ترقية', consumable:'استهلاكي', spare:'قطعة غيار'};
  const typeColor = {upgrade:'var(--accent)', consumable:'var(--sale)', spare:'var(--ink-3)'};

  return (
    <div dir="rtl" style={{background:'var(--bg)', minHeight:'100%', fontFamily:'var(--f-ar)'}}>
      <TopBar/>
      <Header/>
      <div style={{maxWidth:1440, margin:'0 auto', padding:'32px 32px 48px'}}>
        <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:32}}>الرئيسية / الشبكات / Ubiquiti / UDM-Pro</div>
        <div style={{display:'grid', gridTemplateColumns:'1.3fr 1fr', gap:48}}>
          {/* Gallery */}
          <div>
            <div className="ph" style={{aspectRatio:'1/1', marginBottom:12}}>
              <span className="ph-label">UDM-Pro · main</span>
            </div>
            <div style={{display:'grid', gridTemplateColumns:'repeat(5,1fr)', gap:8}}>
              {['front','rear','top','ports','rack'].map((v,i)=>(
                <div key={i} className="ph" style={{aspectRatio:'1/1', cursor:'pointer', border: i===0?'1px solid var(--ink)':'1px solid var(--rule)'}}>
                  <span className="ph-label" style={{fontSize:9}}>{v}</span>
                </div>
              ))}
            </div>
          </div>
          {/* Info */}
          <div>
            <div style={{display:'flex', gap:8, marginBottom:16}}>
              <span className="chip chip-solid">جديد</span>
              <span className="chip chip-accent">● متوفر · 12 وحدة</span>
            </div>
            <div className="mono" style={{color:'var(--ink-4)', fontSize:12, marginBottom:8}}>NG-ENT-003 · UBIQUITI</div>
            <h1 className="t-h2" style={{margin:'0 0 8px', textWrap:'balance'}}>
              Ubiquiti UniFi Dream Machine Pro <span style={{fontWeight:400, color:'var(--ink-3)'}}>(UDM-Pro)</span>
            </h1>
            <p className="en" style={{color:'var(--ink-4)', fontSize:14, margin:0}}>All-in-One Enterprise Network Appliance · 1U · 8-port</p>
            <div className="arabesque-rule" style={{margin:'24px 0', maxWidth:240}}></div>
            <p style={{fontSize:15, color:'var(--ink-2)', lineHeight:1.6, margin:0}}>
              جهاز شبكة موحّد لمشغّلي الشبكات الجادة — يجمع بين راوتر، سويتش 8-منفذ، NVR للمراقبة، ومتحكم UniFi في وحدة 1U واحدة.
            </p>
            {/* Spec table */}
            <div style={{border:'1px solid var(--rule)', marginTop:28}}>
              <div className="mono-up" style={{padding:'10px 16px', borderBottom:'1px solid var(--rule)', background:'var(--paper-2)', color:'var(--ink-3)'}}>SPECS · المواصفات</div>
              {[['CPU','Quad-Core ARM @ 1.7GHz'],['RAM','4GB DDR4'],['Ports','8× 1G · 1× 10G SFP+ · 1× 1G SFP'],['Throughput','3.5 Gbps IDS/IPS'],['Form','1U Rack-mount']].map(([k,v],i,a)=>(
                <div key={i} style={{display:'grid', gridTemplateColumns:'1fr 2fr', padding:'11px 16px', borderBottom: i<a.length-1?'1px dashed var(--rule)':'none'}}>
                  <span className="mono-up" style={{color:'var(--ink-4)'}}>{k}</span>
                  <span className="en" style={{fontSize:13}}>{v}</span>
                </div>
              ))}
            </div>
            {/* Price */}
            <div style={{borderTop:'1px solid var(--ink)', borderBottom:'1px solid var(--ink)', padding:'20px 0', margin:'28px 0', display:'flex', justifyContent:'space-between', alignItems:'flex-end'}}>
              <div>
                <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:4}}>السعر · شامل الضريبة</div>
                <div className="price">
                  <span className="price-now" style={{fontSize:36}}>2,054</span>
                  <span className="price-sar" style={{fontSize:14}}>SAR</span>
                  <span className="price-was">2,570</span>
                </div>
                <div className="mono" style={{fontSize:11, color:'var(--ink-4)', marginTop:4}}>وفّر 516 SAR · 20%</div>
              </div>
              <div className="mono-up" style={{color:'var(--good)'}}>● شحن 2–5 أيام</div>
            </div>
            <div style={{display:'flex', gap:8}}>
              <div style={{display:'flex', border:'1px solid var(--ink)'}}>
                <button style={{padding:'14px 18px', background:'transparent', border:'none', fontSize:16}}>−</button>
                <span className="en" style={{padding:'14px 20px', fontWeight:600, borderInline:'1px solid var(--ink)'}}>1</span>
                <button style={{padding:'14px 18px', background:'transparent', border:'none', fontSize:16}}>+</button>
              </div>
              <button className="btn" style={{flex:1, justifyContent:'center'}}>أضف للسلة · 2,054 SAR</button>
              <button className="btn btn-ghost" style={{border:'1px solid var(--ink)', padding:'14px'}}>♡</button>
            </div>
            <div style={{display:'grid', gridTemplateColumns:'1fr 1fr 1fr', gap:0, marginTop:20, border:'1px solid var(--rule)'}}>
              {[['ضمان','12 شهر'],['إرجاع','14 يوم'],['شحن','GCC']].map(([k,v],i)=>(
                <div key={i} style={{padding:'12px 16px', borderInlineEnd: i<2?'1px solid var(--rule)':'none'}}>
                  <div className="mono-up" style={{color:'var(--ink-4)', marginBottom:4}}>{k}</div>
                  <div style={{fontSize:13, fontWeight:500}}>{v}</div>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* ═══ WORKS BEST WITH ═══ */}
        <div style={{marginTop:72, paddingTop:48, borderTop:'1px solid var(--ink)'}}>
          <div style={{display:'flex', justifyContent:'space-between', alignItems:'flex-end', marginBottom:28}}>
            <div>
              <div className="section-mark" style={{marginBottom:12}}><span>A</span><span style={{color:'var(--ink)'}}>· يعمل بشكل أفضل مع · WORKS BEST WITH</span></div>
              <h2 className="t-h3" style={{margin:0}}>الأجهزة الموصى بها للاستخدام مع UDM-Pro</h2>
            </div>
            <a href="#" style={{fontSize:13, color:'var(--ink)', borderBottom:'1px solid var(--ink)', paddingBottom:2}}>كل التوافقات →</a>
          </div>
          <div style={{display:'grid', gridTemplateColumns:'repeat(4,1fr)', gap:12}}>
            {worksWith.map((w,i)=>(
              <div key={i} style={{border:'1px solid var(--rule)', background:'var(--paper)', position:'relative'}}>
                <div className="ph" style={{aspectRatio:'4/3'}}><span className="ph-label" style={{fontSize:9}}>{w.ph}</span></div>
                <div style={{padding:14, borderTop:'1px solid var(--rule)'}}>
                  <div className="mono" style={{fontSize:10, color:'var(--ink-4)', marginBottom:4}}>{w.sku}</div>
                  <div style={{fontWeight:600, fontSize:13, lineHeight:1.3}}>{w.ar}</div>
                  <div className="en" style={{fontSize:11, color:'var(--ink-4)', marginTop:3}}>{w.en}</div>
                  {/* Why compatible */}
                  <div style={{
                    marginTop:10, padding:'8px 10px',
                    background:'var(--accent-soft)', border:'1px solid var(--rule)',
                    fontSize:11, color:'var(--accent-ink)', lineHeight:1.5
                  }}>
                    <span className="mono-up" style={{color:'var(--accent)', marginInlineEnd:6}}>✓</span>{w.why}
                  </div>
                  <div style={{display:'flex', justifyContent:'space-between', alignItems:'baseline', marginTop:10}}>
                    <div className="price"><span className="en" style={{fontSize:15, fontWeight:600}}>{w.price.toLocaleString('en-US')}</span><span className="price-sar">SAR</span></div>
                    <button className="btn btn-sm" style={{padding:'6px 10px', fontSize:11}}>+ سلة</button>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* ═══ ADD-ONS & REPLACEMENTS ═══ */}
        <div style={{marginTop:56, paddingTop:48, borderTop:'1px solid var(--rule)'}}>
          <div style={{display:'flex', justifyContent:'space-between', alignItems:'flex-end', marginBottom:28}}>
            <div>
              <div className="section-mark" style={{marginBottom:12}}><span>B</span><span style={{color:'var(--ink)'}}>· الإضافات والاستبدال · ADD-ONS & REPLACEMENTS</span></div>
              <h2 className="t-h3" style={{margin:0}}>ملحقات، قطع غيار، وترقيات لجهازك</h2>
            </div>
            <div style={{display:'flex', gap:8}}>
              {['الكل','ترقية','استهلاكي','قطعة غيار'].map((f,i)=>(
                <button key={i} className={i===0?"chip chip-solid":"chip"} style={{padding:'7px 12px', fontSize:11, cursor:'pointer'}}>{f}</button>
              ))}
            </div>
          </div>
          <div style={{display:'grid', gridTemplateColumns:'repeat(3,1fr)', gap:12}}>
            {replacements.map((r,i)=>(
              <div key={i} style={{
                display:'grid', gridTemplateColumns:'80px 1fr', gap:0,
                border:'1px solid var(--rule)', background:'var(--paper)'
              }}>
                <div className="ph" style={{height:'100%'}}><span className="ph-label" style={{fontSize:8}}>{r.ph}</span></div>
                <div style={{padding:14, borderInlineStart:'1px solid var(--rule)'}}>
                  <div style={{display:'flex', gap:6, marginBottom:6}}>
                    <span className="mono" style={{fontSize:10, color:'var(--ink-4)'}}>{r.sku}</span>
                    <span style={{
                      fontFamily:'var(--f-mono)', fontSize:9, padding:'2px 6px', textTransform:'uppercase',
                      border:'1px solid '+typeColor[r.type], color:typeColor[r.type]
                    }}>{typeLabel[r.type]}</span>
                  </div>
                  <div style={{fontWeight:600, fontSize:13, lineHeight:1.3}}>{r.ar}</div>
                  <div className="en" style={{fontSize:11, color:'var(--ink-4)', marginTop:2}}>{r.en}</div>
                  <div style={{display:'flex', justifyContent:'space-between', alignItems:'baseline', marginTop:10}}>
                    <div className="price"><span className="en" style={{fontSize:15, fontWeight:600}}>{r.price.toLocaleString('en-US')}</span><span className="price-sar">SAR</span></div>
                    <button className="btn btn-sm" style={{padding:'5px 9px', fontSize:11}}>أضف +</button>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Tabs */}
        <div style={{marginTop:56, borderTop:'1px solid var(--ink)', paddingTop:32}}>
          <div style={{display:'flex', gap:32, marginBottom:32, borderBottom:'1px solid var(--rule)'}}>
            {['الوصف','المواصفات الكاملة','المراجعات (24)','الشحن والإرجاع'].map((t,i)=>(
              <button key={i} onClick={()=>setActiveTab(i)} style={{
                paddingBottom:12,
                fontWeight: activeTab===i?600:400, color: activeTab===i?'var(--ink)':'var(--ink-4)',
                fontSize:14, cursor:'pointer', background:'transparent', border:'none',
                borderBottom: activeTab===i?'2px solid var(--ink)':'2px solid transparent'
              }}>{t}</button>
            ))}
          </div>

          {/* Description */}
          {activeTab===0 && (
          <div style={{display:'grid', gridTemplateColumns:'2fr 1fr', gap:48}}>
            <div style={{fontSize:15, lineHeight:1.7, color:'var(--ink-2)'}}>
              <p>UDM-Pro هو جهاز شبكة احترافي مصمّم للمكاتب والشركات الصغيرة ومستخدمي الهوم لاب الجادين. يجمع بين أربعة أجهزة في وحدة 1U واحدة: راوتر آمن مع IDS/IPS بسرعة 3.5 جيجابت/ثانية، سويتش مُدار 8-منفذ مع منفذ 10G SFP+، NVR للمراقبة بالفيديو، ومتحكم UniFi لإدارة الشبكة بالكامل.</p>
              <p>اختياره يعني التخلّي عن تعقيد الأجهزة المتعددة لصالح إدارة موحّدة عبر واجهة UniFi، مع أداء يكفي لشبكات تصل إلى 1000 جهاز.</p>
            </div>
            <div style={{border:'1px solid var(--rule)', padding:20}}>
              <div className="mono-up" style={{color:'var(--ink-3)', marginBottom:12}}>في الصندوق</div>
              <ul style={{listStyle:'none', padding:0, margin:0, display:'flex', flexDirection:'column', gap:8}}>
                {['UDM-Pro × 1','كابل طاقة C13','أقواس Rack','دليل البدء السريع'].map((x,i)=>(
                  <li key={i} style={{fontSize:13, display:'flex', justifyContent:'space-between', borderBottom:'1px dashed var(--rule)', paddingBottom:6}}>
                    <span>{x}</span>
                    <span className="mono" style={{color:'var(--ink-4)'}}>0{i+1}</span>
                  </li>
                ))}
              </ul>
            </div>
          </div>
          )}

          {/* Reviews */}
          {activeTab===2 && (
          <div>
            <div style={{display:'grid', gridTemplateColumns:'180px 1fr', gap:32, marginBottom:24, padding:20, background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)'}}>
              <div style={{textAlign:'center', display:'flex', flexDirection:'column', alignItems:'center', justifyContent:'center', gap:6}}>
                <div style={{fontFamily:'var(--f-wordmark)', fontSize:56, fontWeight:700, color:'var(--indigo)', lineHeight:1}}>4.8</div>
                <div style={{display:'flex', gap:2, fontSize:18}}>{[1,2,3,4,5].map(s=><span key={s} style={{color:'#F59E0B'}}>★</span>)}</div>
                <div className="mono-up" style={{color:'var(--dim)', fontSize:9}}>24 مراجعة</div>
              </div>
              <div style={{display:'flex', flexDirection:'column', gap:6, justifyContent:'center'}}>
                {[[5,80],[4,15],[3,5],[2,0],[1,0]].map(([star,pct],i)=>(
                  <div key={i} style={{display:'flex', alignItems:'center', gap:10}}>
                    <span style={{fontFamily:'var(--f-mono)', fontSize:11, color:'var(--dim)', width:18}}>{star}★</span>
                    <div style={{flex:1, height:7, background:'var(--surface-2)', borderRadius:4}}>
                      <div style={{width:`${pct}%`, height:'100%', background:'#F59E0B', borderRadius:4}}/>
                    </div>
                    <span className="mono" style={{fontSize:10, color:'var(--dim)', width:28}}>{pct}%</span>
                  </div>
                ))}
              </div>
            </div>
            <div style={{display:'flex', flexDirection:'column', gap:12}}>
              {[
                {name:'محمد العتيبي', rating:5, date:'15 أبريل 2026', text:'جهاز ممتاز جداً. ركّبته في مكتبي وربطت فيه 4 نقاط U6 Pro. الإدارة سلسة جداً. نيوجن وصّلوا خلال يومين.', helpful:12},
                {name:'خالد الدوسري', rating:5, date:'2 مارس 2026',  text:'من أفضل ما اشتريته. الـ IDS/IPS شغّالة. فريق نيوجن ساعدوني في الإعداد على واتساب.', helpful:8},
                {name:'سارة الحربي',  rating:4, date:'20 فبراير 2026',text:'جيد جداً لكن الإعداد يحتاج خبرة. الدعم من نيوجن كان ممتاز وحل المشكلة خلال ساعة.', helpful:5},
              ].map((r,i)=>(
                <div key={i} style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', padding:20}}>
                  <div style={{display:'flex', justifyContent:'space-between', alignItems:'flex-start', marginBottom:10}}>
                    <div style={{display:'flex', gap:12, alignItems:'center'}}>
                      <div style={{width:36, height:36, borderRadius:'50%', background:`linear-gradient(135deg,var(--indigo),var(--accent))`, display:'grid', placeItems:'center', fontFamily:'var(--f-wordmark)', fontWeight:700, fontSize:13, color:'#fff'}}>{r.name[0]}</div>
                      <div>
                        <div style={{fontWeight:600, fontSize:14, color:'var(--indigo)'}}>{r.name}</div>
                        <div style={{display:'flex', gap:2, marginTop:2}}>{[1,2,3,4,5].map(s=><span key={s} style={{fontSize:12, color:s<=r.rating?'#F59E0B':'var(--rule-strong)'}}>★</span>)}</div>
                      </div>
                    </div>
                    <div style={{display:'flex', gap:8, alignItems:'center'}}>
                      <span className="chip chip-accent" style={{fontSize:8}}>✓ موثّق</span>
                      <span className="mono" style={{fontSize:10, color:'var(--dim)'}}>{r.date}</span>
                    </div>
                  </div>
                  <p style={{margin:'0 0 10px', fontSize:14, color:'var(--ink-2)', lineHeight:1.6}}>{r.text}</p>
                  <div style={{display:'flex', gap:6, alignItems:'center'}}>
                    <span style={{fontSize:12, color:'var(--dim)'}}>مفيدة؟</span>
                    <button className="chip" style={{padding:'3px 8px', fontSize:11, cursor:'pointer'}}>👍 {r.helpful}</button>
                    <button className="chip" style={{padding:'3px 8px', fontSize:11, cursor:'pointer'}}>👎</button>
                  </div>
                </div>
              ))}
            </div>
          </div>
          )}

          {/* Full specs */}
          {activeTab===1 && (
          <div style={{display:'grid', gridTemplateColumns:'1fr 1fr', gap:16}}>
            {[
              {title:'المعالج والذاكرة', specs:[['CPU','Quad-Core ARM @ 1.7GHz'],['RAM','4GB DDR4'],['Flash','16GB eMMC'],['OS','UniFi OS']]},
              {title:'المنافذ والشبكة',  specs:[['WAN','1× 10G SFP+'],['LAN','8× GbE RJ45'],['Extra SFP','1× 1G'],['Throughput','3.5 Gbps IDS/IPS']]},
              {title:'الفيزيائية',       specs:[['Form','1U Rack-mount'],['Dims','442×43.7×285mm'],['Weight','2.5 kg'],['Power','50W max']]},
              {title:'الأمان',           specs:[['Firewall','Stateful + IDS/IPS'],['VPN','WireGuard · OpenVPN'],['Mgmt','UniFi App'],['NVR','4 cameras']]},
            ].map((s,i)=>(
              <div key={i} style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', overflow:'hidden'}}>
                <div style={{padding:'10px 16px', background:'var(--surface-2)', borderBottom:'1px solid var(--rule)'}}>
                  <span className="mono-up" style={{color:'var(--ink-4)'}}>{s.title}</span>
                </div>
                {s.specs.map(([k,v],j)=>(
                  <div key={j} style={{display:'grid', gridTemplateColumns:'1fr 2fr', padding:'10px 16px', borderBottom:j<3?'1px dashed var(--rule)':'none'}}>
                    <span className="mono-up" style={{color:'var(--dim)', fontSize:9}}>{k}</span>
                    <span style={{fontFamily:'var(--f-wordmark)', fontSize:12}}>{v}</span>
                  </div>
                ))}
              </div>
            ))}
          </div>
          )}

          {/* Shipping tab */}
          {activeTab===3 && (
          <div style={{display:'grid', gridTemplateColumns:'1fr 1fr', gap:16}}>
            {[
              {title:'الشحن والتوصيل', items:[['الرياض','1–2 يوم · Aramex'],['المملكة','2–5 أيام · SMSA'],['خليج GCC','3–7 أيام · DHL'],['مجاني','فوق 500 SAR']]},
              {title:'الإرجاع والاستبدال', items:[['مدة الإرجاع','14 يوم من الاستلام'],['الحالة','غير مستخدم · عبوة أصلية'],['التواصل','support@neogen.store'],['استرداد','5–7 أيام عمل']]},
            ].map((s,i)=>(
              <div key={i} style={{background:'var(--surface)', border:'1px solid var(--rule)', borderRadius:'var(--r-2)', overflow:'hidden'}}>
                <div style={{padding:'12px 16px', background:'var(--surface-2)', borderBottom:'1px solid var(--rule)'}}>
                  <span className="mono-up" style={{color:'var(--ink-4)'}}>{s.title}</span>
                </div>
                {s.items.map(([k,v],j)=>(
                  <div key={j} style={{display:'grid', gridTemplateColumns:'1fr 1.5fr', padding:'12px 16px', borderBottom:j<3?'1px dashed var(--rule)':'none'}}>
                    <span className="mono-up" style={{color:'var(--dim)', fontSize:9}}>{k}</span>
                    <span style={{fontSize:13, color:'var(--ink-2)'}}>{v}</span>
                  </div>
                ))}
              </div>
            ))}
          </div>
          )}
        </div>
      </div>
      <Footer/>
    </div>
  );
};
window.PDP = PDP;
