<template>
  <!--
    作者：luoyiming
    时间：2019-10-25
    描述：权限管理-管理员列表-新增管理员
  -->
  <el-dialog title="添加管理员" v-model="dialogVisible" @close="dialogFormVisible" :close-on-click-modal="false" :close-on-press-escape="false">
    <el-form size="small" ref="form" :model="form" :rules="formRules" :label-width="formLabelWidth">
      <el-form-item label="用户名" prop="user_name"><el-input v-model="form.user_name" placeholder="请输入用户名"></el-input></el-form-item>
      <el-form-item label="所属角色" prop="role_id">
        <el-select v-model="form.role_id" :multiple="true">
          <el-option v-for="item in roleList" :value="item.role_id" :key="item.role_id" :label="item.role_name_h1"></el-option>
        </el-select>
      </el-form-item>
      <el-form-item label="登录密码" prop="password">
        <el-input v-model="form.password" placeholder="请输入登录密码" type="password"></el-input>
        <p class="gray">密码需为 8-20 位，且至少包含字母、数字、符号中的 2 种</p>
      </el-form-item>
      <el-form-item label="确认密码" prop="confirm_password"><el-input v-model="form.confirm_password" placeholder="请输入确认密码" type="password"></el-input></el-form-item>
      <el-form-item label="姓名" prop="real_name"><el-input v-model="form.real_name"></el-input></el-form-item>
    </el-form>
    <template #footer>
    <div class="dialog-footer">
      <el-button @click="dialogVisible = false">取消</el-button>
      <el-button type="primary" @click="onSubmit" :loading="loading">确定</el-button>
    </div>
    </template>
  </el-dialog>
</template>

<script>
import AuthApi from '@/api/auth.js';
export default {
  data() {
    let checkPassword = (rule, value, callback) => {
      let result = this.$filter.checkPassword(value);
      if (!result.valid) {
        callback(new Error(result.message));
      } else {
        callback();
      }
    };
    let checkPasswordConfirm = (rule, value, callback) => {
      if (!value) {
        callback(new Error('请输入确认密码'));
      } else if (value !== this.form.password) {
        callback(new Error('确认密码不一致'));
      } else {
        callback();
      }
    };
    return {
      /* 左边宽度 */
      formLabelWidth: '120px',
      /* 是否显示 */
      loading: false,
      /* 是否显示 */
      dialogVisible: false,
      /* 表单对象 */
      form: {
        user_name: '',
        access_id: []
      },
      /* 表单验证 */
      formRules: {
        user_name: [
          {
            required: true,
            message: '请输入用户名',
            trigger: 'blur'
          }
        ],
        role_id: [
          {
            required: true,
            message: '请选择所属角色',
            trigger: 'blur'
          }
        ],
        password: [
          {
            validator: checkPassword,
            required: true,
            trigger: 'blur'
          }
        ],
        confirm_password: [
          {
            validator: checkPasswordConfirm,
            required: true,
            trigger: 'blur'
          }
        ],
        real_name: [
          {
            required: true,
            message: '请输入姓名',
            trigger: 'blur'
          }
        ]
      }
    };
  },
  props: ['open', 'roleList'],
  watch: {
    open: function(n, o) {
      if (n != o) {
        this.dialogVisible = this.open;
      }
    }
  },
  created() {},
  methods: {
    /* 添加 */
    onSubmit() {
      let self = this;
      let params = self.form;
      self.$refs.form.validate((valid) => {
        if (valid) {
          self.loading = true;
          AuthApi.userAdd(params, true)
            .then(data => {
              self.loading = false;
              ElMessage({
                message: '恭喜你，添加成功',
                type: 'success'
              });
              self.dialogFormVisible(true);
            })
            .catch(error => {
              self.loading = false;
            });
        }
      });
    },

    /* 关闭弹窗 */
    dialogFormVisible(e) {
      if (e) {
        this.$emit('close', {
          type: 'success',
          openDialog: false
        });
      } else {
        this.$emit('close', {
          type: 'error',
          openDialog: false
        });
      }
    }
  }
};
</script>

<style></style>
