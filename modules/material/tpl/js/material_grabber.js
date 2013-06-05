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
        st='style',
        wi='width',
        owi='offsetWidth',
        he='height',
        ohe='offsetHeight',
        wm='wmode',
        oq='opaque',
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

        // get the original width and height of object
        function owh(o){
            //Fix overlap issue for Safari
            o.setAttribute(wm, oq);

            //Add style to object
            var width=o[owi];
            if(!width) width=560;
            o[st][wi]=width + 'px';
            if(o[wi]) o.removeAttribute(wi);

            var height=o[ohe];
            if(!height) height=315;
            o[st][he]=height + 'px';
            if(o[he]) o.removeAttribute(he);

            return o;
        }

        // add embed to tag into object tag
        function aeo(o){
            var nodes=o.childNodes;
            var emb=document.createElement('embed');
            var emb_src='';
            for(i=0; i<nodes.length; i++){
                var node=nodes[i];
                if(node.tagName && node.tagName != 'undefined'){
                    if(node.tagName.toLowerCase() == 'embed'){
                        return o;
                    }
                    else if(node.tagName.toLowerCase() == 'param'){
                        emb.setAttribute(node.name, node.value);
                        if(node.name.toLowerCase()=='movie'){
                            emb_src=node.value;
                        }
                    }
                }
            }
            if(!emb_src && o.hasAttribute('data')) emb_src=o.getAttribute('data');
            if(emb_src) emb.setAttribute('src', emb_src);
            return emb;
        }

	// grab objects
	function ob(d){
		var e=d[gt]('embed'),o=d[gt]('object'),i;
		for(i=0;i<e[l];i++){
                    if(e[i][pn]&&e[i][pn].tagName.toLowerCase()!='object'){
                        var tmp_o=owh(e[i]);
                        s.o.push(goh(tmp_o));
                    }
                }
		for(i=0;i<o[l];i++){
                    var tmp_o=aeo(o[i]);
                    var tmp_o2=owh(tmp_o);
                    s.o.push(goh(tmp_o2));
                }
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
    var ki = inp=ie?ce('<input type="hidden" name="'+form_key_name+'" value="' + form_key +'">'):ce('input');ie?0:inp[sa]('type','hidden')+inp[sa]('name',form_key_name)+inp[sa]('value',form_key);
    f[ac](ki);
	di[ac](f);d.body[ac](di);f.submit();
	if(ie)d.charset=c;
})();
