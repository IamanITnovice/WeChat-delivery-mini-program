<template>

	<div>
		<el-dialog title="二维码" width="35%" v-model="dialogVisible" @close="dialogFormVisible"
			:close-on-click-modal="false" :close-on-press-escape="false">
			<el-form size="small">
				<el-form-item label="下载类型" :label-width="formLabelWidth">
					<el-radio-group v-model="source" @change="sourceClick">
						<el-radio label="wx">微信小程序</el-radio>
						<el-radio label="mp">公众号，H5网页</el-radio>
					</el-radio-group>
				</el-form-item>
				<el-form-item label="" :label-width="formLabelWidth">
					<img :src="image" width="120px;" />
				</el-form-item>
				<el-form-item label="" :label-width="formLabelWidth">
					<a :href="image" v-if="image" rel="external nofollow" download>下载二维码（桌位编码:{{table_no}}）</a>
				</el-form-item>
			</el-form>
			<template #footer>
				<div class="dialog-footer">
					<el-button @click="dialogFormVisible">关 闭</el-button>
				</div>
			</template>
		</el-dialog>
	</div>
</template>

<script>
	import StoreApi from '@/api/store.js';
	import qs from 'qs';
	import {
		useUserStore
	} from '@/store';
	export default {
		data() {
			return {
				/*左边长度*/
				formLabelWidth: '120px',
				/*是否显示*/
				dialogVisible: false,
				loading: false,
				source: 'wx',
				image: ''
			};
		},
		props: ['open', 'code_id', 'table_no'],
		watch: {
			open: function(n, o) {
				this.dialogVisible = this.open;
				if (this.dialogVisible) {
					this.getQrcode();
				}
			}
		},
		created() {},
		methods: {
			sourceClick() {
				this.getQrcode();
			},
			/*获取列表*/
			getQrcode() {
				let self = this;
				StoreApi.tableQrcode({
						id: self.code_id,
						source: self.source,
					}, true)
					.then(res => {
						self.loading = false;
						self.image = res.data.image;
					})
					.catch(error => {
						self.loading = false;
					});
			},
			/*关闭弹窗*/
			dialogFormVisible(e) {
				if (e) {
					this.$emit('close', true);
				} else {
					this.$emit('close', false);
				}
			}
		}
	};
</script>

<style></style>