const filters = {

  /*测试过滤*/
  testFilter:function(val){
    return 'vip'+val;
  },

	/*判断字段是否为空，为空的话先转-*/
	isNull:function(val){

		if(val==null||val==undefined||val===""||val==="null"||val=='undefined'){
			return '-';
		}else if(val==0 || val=='0'){
			return val;
		}
		else{
			return val;
		}
	},

	/*百分比*/
	returnPercentage:function(val){
		if(val!==null&&val!==''&&val!==undefined){
			let num=(val*100).toFixed(2);
			return num+'%';
		}else{
			return '-';
		}
	},

	/*小数点后面保留位数*/
	returnToFixed:function(val,num){
		if(val!=null){
			let nums=val.toFixed(num);
			return nums;
		}else{
			if(val!=null&&val!==""){
				return val;
			}else{
				return '-';
			}
		}
	},

	/*取万元*/
	tenThousand:function(val){
		if(val!=null&&val!=''){
			var x=(val/10000).toFixed(2);
			var f = Math.round(x * 100) / 100;
   			var s = f.toString();
			return s;
		}else{
			return '-';
		}
	},

	/*数字换成周*/
	numTabWeek: function(val) {

		let ch = '';
		switch(val) {
			case 1:
				ch = '一';
				break;
			case 2:
				ch = '二';
				break;
			case 3:
				ch = '三';
				break;
			case 4:
				ch = '四';
				break;
			case 5:
				ch = '五';
				break;
			case 6:
				ch = '六';
				break;
			case 7:
				ch = '日';
				break;
		}
		return ch;

	},

	/*返回性别*/
	convertSex: function(num){
		let sex = '';
		switch(num) {
			case 0:
				sex = '女';
				break;
			case 1:
				sex= '男';
				break;
			default:
				sex = '其他';
		}
		return sex;
	},
  
  /*去除空格*/
  replaceSpace:function(val){
  	if(val!=undefined){
  		return val.replace(/\s*/g,"");
  	}else{
  		return '';
  	}
  },
  
  /*判断有没有空格*/
  hasSpace:function(val){
  	if(val!=undefined){
  		let patt=/\s/g;
  		return patt.test(val);
  	}else{
  		return false;
  	}
  },

  /*统一密码强度校验*/
  checkPassword:function(val,required=true){
  	if((val===undefined||val===null||val==='')&&required){
  		return {
  			valid:false,
  			message:'请输入密码'
  		};
  	}
  	if(val===undefined||val===null||val===''){
  		return {
  			valid:true,
  			message:''
  		};
  	}
  	if(this.hasSpace(val)){
  		return {
  			valid:false,
  			message:'密码不能包含空格'
  		};
  	}
  	if(val.length<8||val.length>20){
  		return {
  			valid:false,
  			message:'密码长度需为8-20位'
  		};
  	}
  	let level=0;
  	/[A-Za-z]/.test(val)&&level++;
  	/\d/.test(val)&&level++;
  	/[^A-Za-z\d]/.test(val)&&level++;
  	if(level<2){
  		return {
  			valid:false,
  			message:'密码需至少包含字母、数字、符号中的2种'
  		};
  	}
  	return {
  		valid:true,
  		message:''
  	};
  },
  
  /*密码必须由数字和字母组成*/
  passwordForm(val){
  	var reg = new RegExp(/^(?![^a-zA-Z]+$)(?!\D+$)/);
  	if (reg.test(val)){
  		return true;
  	}else{
  		return false;
  	}
  },
  
  /*判断字符串是否全是空格*/
  isAllSpace(val){
  	if(val.match(/^[ ]*$/)){
  		return true;
  	}else{
  		return false;
  	}
  }


}

export default filters;
