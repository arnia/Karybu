(function(){
	var w=window,
		d=document,
		lo=w.location.href,
		ti=d.title,
		ac='appendChild',
		sa='setAttribute',
		gt='getElementsByTagName',
		v='value',
		l='length',
		u='utf-8',
		pn='parentNode',
		ih='innerHTML',
		oh='outerHTML',
		c=d.charset,
		ie=('\v'=='v'),
		pr=w.__xe_root+'&auth='+w.auth,
		s={i:[],t:[],o:[],ti:[ti],u:[lo]},
		h={i:'images',t:'text',o:'objects',ti:'title',u:'url'},
		ds=[],di,f,inp;
	
	if (!pr) return;

	function ce(s){return d.createElement(s)};
	
	// grab images
	function im(d){
		var is=d.images;
		for(var i=0;i<is[l];i++){
			if(is[i].src){
				s.i.push(is[i].src);
			}
		}
	};

	
	// grab text of selection
	function tx(d){
		var t;try{t=ie?d.selection.createRange().text:d.defaultView.getSelection().getRangeAt(0).toString();if(t)s.t.push(t)}catch(e){}
	};
	
	// get outer html
	di = ce('div');
	function goh(e){
		if(e[oh])return e[oh];
		var t;di[ac](e.cloneNode(true));t=di[ih];di[ih]='';return t;
	};

	// grab objects
	function ob(d){
		var e=d[gt]('embed'),o=d[gt]('object'),i;
		for(i=0;i<e[l];i++)(e[i][pn]&&e[i][pn].tagName.toLowerCase()=='object')?0:s.o.push(goh(e[i]));
		for(i=0;i<o[l];i++)s.o.push(goh(o[i]));
	};

	// find all frames
	(function ff(d){
		var f1=d[gt]('frame'),f2=d[gt]('iframe'),i;
		ds.push(d);
		for(i=0;i<f1[l];i++)try{ff(f1[i].contentDocument||f1[i].contentWindow.document)}catch(e){};
		for(i=0;i<f2[l];i++)try{ff(f2[i].contentDocument||f2[i].contentWindow.document)}catch(e){};
	})(d);

	for(var i=0;i<ds[l];i++) im(ds[i])+tx(ds[i])+ob(ds[i]);

	di[sa]('style','position:absolute;overflow:hidden;width:1px;height:1px;left:-9px;top:-9px;');
	f=ce('form');f[sa]('action',pr);f[sa]('target','XE_materialGrabWin');f[sa]('method','POST');ie?d.charset=u:f[sa]('accept-charset',u);
	for(k in s){
		if (!h[k]) continue;
		inp=ie?ce('<input type="hidden" name="'+h[k]+'">'):ce('input');ie?0:inp[sa]('type','hidden')+inp[sa]('name',h[k]);
		inp.value=s[k].join('\t');
		f[ac](inp);
	}
	di[ac](f);d.body[ac](di);f.submit();
	if(ie)d.charset=c;
})();
