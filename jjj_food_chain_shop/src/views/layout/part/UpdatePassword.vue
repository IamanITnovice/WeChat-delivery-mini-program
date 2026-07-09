<template>
	<el-dialog title="修改密码" v-model="dialogVisible" @close="dialogFormVisible" :close-on-click-modal="false"
		:close-on-press-escape="false" width="30%">
		<el-form size="small" :model="form" ref="form" :rules="rules">
			<el-form-item label="原始密码" :label-width="formLabelWidth" prop="oldpass">
				<el-input type="password" v-model="form.oldpass" autocomplete="off"></el-input>
			</el-form-item>
			<el-form-item label="新密码" :label-width="formLabelWidth" prop="password">
				<el-input type="password" v-model="form.password" autocomplete="off"></el-input>
				<p class="gray">密码需为8-20位，且至少包含字母、数字、符号中的2种</p>
			</el-form-item>
			<el-form-item label="确认新密码" :label-width="formLabelWidth" prop="confirmPass">
				<el-input type="password" v-model="form.confirmPass" autocomplete="off"></el-input>
			</el-form-item>
		</el-form>
		<template #footer>
			<el-button @click="dialogFormVisible">取消</el-button>
			<el-button type="primary" @click="submitFunc(form.user_id)" :loading="loading">确定</el-button>
		</template>
	</el-dialog>
</template>

<script>
import UserApi from '@/api/user.js';
export default {
	data() {
		let checkOldPass = (rule, value, callback) => {
			if (!value) {
				callback(new Error('请输入原始密码'));
			} else {
				callback();
			}
		};
		let checkPassword = (rule, value, callback) => {
			let result = this.$filter.checkPassword(value);
			if (!result.valid) {
				callback(new Error(result.message));
			} else {
				callback();
			}
		};
		let checkConfirmPass = (rule, value, callback) => {
			if (!value) {
				callback(new Error('请输入确认新密码'));
			} else if (value !== this.form.password) {
				callback(new Error('两次输入密码不一致'));
			} else {
				callback();
			}
		};
		return {
			loading: false,
			/*左边长度*/
			formLabelWidth: '100px',
			/*是否显示*/
			dialogVisible: true,
			/*表单*/
			form: {
				oldpass: '',
				password: '',
				confirmPass: ''
			},
			rules: {
				oldpass: [{
					validator: checkOldPass,
					required: true,
					trigger: 'blur'
				}],
				password: [{
					validator: checkPassword,
					required: true,
					trigger: 'blur'
				}],
				confirmPass: [{
					validator: checkConfirmPass,
					required: true,
					trigger: 'blur'
				}]
			}
		};
	},
	props: [],
	created() {},
	methods: {
		/*确认事件*/
		submitFunc(e) {
			let self = this;
			let form = self.form;
			self.$refs.form.validate((valid) => {
				if (valid) {
					self.loading = true;
					UserApi.EditPass(form, true).then(data => {
						self.loading = false;
						if (data.code == 1) {
							ElMessage({
								message: data.msg,
								type: 'success'
							});
							self.dialogFormVisible(true);
						} else {
							self.loading = false;
						}
					})
						.catch(error => {
							self.loading = false;
						});
				}
			});
		},

		/*关闭弹窗*/
		dialogFormVisible() {
			this.$emit('close', false);
		}
	}
};
</script>

<style></style>
