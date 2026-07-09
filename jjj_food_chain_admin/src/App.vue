<template>
	<el-config-provider :locale="locale">
		<router-view />
	</el-config-provider>
</template>
<script>
	import {
		reactive,
		toRefs,
		defineComponent
	} from 'vue';
	import {
		ElConfigProvider
	} from 'element-plus';
	//import zhCn from 'element-plus/lib/locale/lang/zh-cn';
	import zhCn from "element-plus/es/locale/lang/zh-cn";
	export default defineComponent({
		components: {
			[ElConfigProvider.name]: ElConfigProvider,
		},
		setup() {
			let locale = zhCn;
			const state = reactive({});
			return {
				...toRefs(state),
				locale,
			};
		}
	});
</script>
<style lang="scss">
	@use './assets/font/iconfont.css';
	* {
		margin: 0;
		padding: 0;
		font-size: 12px;
	}

	.el-form--inline .el-select {
		min-width: 160px;
	}

	.el-form--inline .el-input,
	.el-form--inline .el-select,
	.el-form--inline .el-date-editor,
	.el-form--inline .el-cascader,
	.el-dialog .el-input,
	.el-dialog .el-select,
	.el-dialog .el-date-editor,
	.el-dialog .el-cascader,
	.el-form:not(.el-form--inline) .el-input,
	.el-form:not(.el-form--inline) .el-select,
	.el-form:not(.el-form--inline) .el-date-editor,
	.el-form:not(.el-form--inline) .el-cascader {
		--el-component-size-small: 32px;
	}

	.el-form--inline .el-input__wrapper,
	.el-form--inline .el-select .el-input__wrapper,
	.el-form--inline .el-date-editor .el-input__wrapper,
	.el-form--inline .el-cascader .el-input__wrapper,
	.el-dialog .el-input__wrapper,
	.el-dialog .el-select .el-input__wrapper,
	.el-dialog .el-date-editor .el-input__wrapper,
	.el-dialog .el-cascader .el-input__wrapper,
	.el-form:not(.el-form--inline) .el-input__wrapper,
	.el-form:not(.el-form--inline) .el-select .el-input__wrapper,
	.el-form:not(.el-form--inline) .el-date-editor .el-input__wrapper,
	.el-form:not(.el-form--inline) .el-cascader .el-input__wrapper {
		height: 32px;
		min-height: 32px;
	}

	.el-dialog .el-input,
	.el-dialog .el-select,
	.el-dialog .el-date-editor,
	.el-dialog .el-cascader,
	.el-dialog .el-textarea,
	.el-form:not(.el-form--inline) .el-input:not(.el-date-editor),
	.el-form:not(.el-form--inline) .el-select,
	.el-form:not(.el-form--inline) .el-date-editor,
	.el-form:not(.el-form--inline) .el-cascader,
	.el-form:not(.el-form--inline) .el-textarea {
		width: 100%;
		max-width: 460px;
	}

	.el-select .el-input__wrapper {
		border-radius: 6px;
		background-color: #fff;
		box-shadow: 0 0 0 1px #dcdfe6 inset;
		transition: box-shadow 0.2s ease, background-color 0.2s ease;
	}

	.el-select:hover .el-input__wrapper {
		box-shadow: 0 0 0 1px #c0c4cc inset;
	}

	.el-select.is-focus .el-input__wrapper,
	.el-select .el-input.is-focus .el-input__wrapper {
		box-shadow: 0 0 0 1px var(--el-color-primary) inset;
	}

	.el-select .el-select__placeholder,
	.el-select .el-input__inner {
		color: #606266;
	}

	.el-select .el-tag {
		border-radius: 4px;
	}

	.el-popper.el-select__popper {
		border: 1px solid #ebeef5;
		border-radius: 10px;
		box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
	}

	.el-select-dropdown__list {
		padding: 6px;
	}

	.el-select-dropdown__item {
		height: 36px;
		line-height: 36px;
		padding: 0 12px;
		border-radius: 6px;
	}

	.el-select-dropdown__item.hover,
	.el-select-dropdown__item:hover {
		background-color: var(--el-color-primary-light-9);
	}

	.el-select-dropdown__item.selected {
		color: var(--el-color-primary);
		font-weight: 500;
		background-color: var(--el-color-primary-light-9);
	}
</style>
