const app = getApp()

Page({
  data: {
    recipeId: 0,
    categories: [],
    formData: {
      title: '',
      category_id: '',
      description: '',
      image: '',
      images: [],
      materials: [],
      steps: [],
      tips: '',
      suitable_people: '',
      cooking_time: '',
      difficulty: 1
    },
    materialName: '',
    materialAmount: '',
    stepDesc: '',
    stepImage: '',
    loading: true,
    submitting: false
  },

  onLoad(options) {
    const id = options.id ? parseInt(options.id) : 0
    if (id) {
      this.setData({ recipeId: id })
      this.loadRecipeDetail()
    } else {
      wx.showToast({
        title: '参数错误',
        icon: 'none'
      })
      setTimeout(() => {
        wx.navigateBack()
      }, 1500)
    }
  },

  async loadCategories() {
    try {
      const res = await app.request('/category/list')
      const categories = (res.data || []).filter(item => item.id > 0)
      this.setData({ categories })
    } catch (e) {
      console.error('加载分类失败', e)
    }
  },

  async loadRecipeDetail() {
    this.setData({ loading: true })
    
    try {
      const res = await app.request('/recipe/detail', { id: this.data.recipeId })
      
      if (res.data) {
        const recipe = res.data
        this.setData({
          formData: {
            title: recipe.title || '',
            category_id: recipe.category_id || '',
            description: recipe.description || '',
            image: recipe.image || '',
            images: recipe.images || [],
            materials: recipe.materials || [],
            steps: recipe.steps || [],
            tips: recipe.tips || '',
            suitable_people: recipe.suitable_people || '',
            cooking_time: recipe.cooking_time || '',
            difficulty: recipe.difficulty || 1
          },
          loading: false
        })
        
        this.loadCategories()
      }
    } catch (e) {
      this.setData({ loading: false })
      console.error('加载菜谱详情失败', e)
    }
  },

  onInputChange(e) {
    const field = e.currentTarget.dataset.field
    const value = e.detail.value
    this.setData({
      [`formData.${field}`]: value
    })
  },

  onCategoryChange(e) {
    const index = e.detail.value
    const categories = this.data.categories
    this.setData({
      'formData.category_id': categories[index].id
    })
  },

  onDifficultyChange(e) {
    const value = parseInt(e.detail.value) + 1
    this.setData({
      'formData.difficulty': value
    })
  },

  onMaterialNameInput(e) {
    this.setData({ materialName: e.detail.value })
  },

  onMaterialAmountInput(e) {
    this.setData({ materialAmount: e.detail.value })
  },

  addMaterial() {
    const name = this.data.materialName.trim()
    const amount = this.data.materialAmount.trim()
    
    if (!name) {
      wx.showToast({
        title: '请输入食材名称',
        icon: 'none'
      })
      return
    }
    
    const materials = [...this.data.formData.materials]
    materials.push({
      name: name,
      amount: amount || '适量'
    })
    
    this.setData({
      'formData.materials': materials,
      materialName: '',
      materialAmount: ''
    })
  },

  removeMaterial(e) {
    const index = e.currentTarget.dataset.index
    const materials = [...this.data.formData.materials]
    materials.splice(index, 1)
    this.setData({
      'formData.materials': materials
    })
  },

  onStepDescInput(e) {
    this.setData({ stepDesc: e.detail.value })
  },

  async chooseStepImage() {
    try {
      const res = await wx.chooseMedia({
        count: 1,
        mediaType: ['image'],
        sourceType: ['album', 'camera']
      })
      
      const tempFilePath = res.tempFiles[0].tempFilePath
      this.setData({ stepImage: tempFilePath })
    } catch (e) {
      console.error('选择图片失败', e)
    }
  },

  addStep() {
    const desc = this.data.stepDesc.trim()
    const image = this.data.stepImage
    
    if (!desc) {
      wx.showToast({
        title: '请输入步骤描述',
        icon: 'none'
      })
      return
    }
    
    const steps = [...this.data.formData.steps]
    steps.push({
      order: steps.length + 1,
      desc: desc,
      image: image || ''
    })
    
    this.setData({
      'formData.steps': steps,
      stepDesc: '',
      stepImage: ''
    })
  },

  removeStep(e) {
    const index = e.currentTarget.dataset.index
    const steps = [...this.data.formData.steps]
    steps.splice(index, 1)
    
    steps.forEach((step, i) => {
      step.order = i + 1
    })
    
    this.setData({
      'formData.steps': steps
    })
  },

  async chooseMainImage() {
    try {
      const res = await wx.chooseMedia({
        count: 1,
        mediaType: ['image'],
        sourceType: ['album', 'camera']
      })
      
      const tempFilePath = res.tempFiles[0].tempFilePath
      this.setData({
        'formData.image': tempFilePath
      })
    } catch (e) {
      console.error('选择图片失败', e)
    }
  },

  async chooseMoreImages() {
    try {
      const res = await wx.chooseMedia({
        count: 9,
        mediaType: ['image'],
        sourceType: ['album', 'camera']
      })
      
      const tempFilePaths = res.tempFiles.map(file => file.tempFilePath)
      const images = [...this.data.formData.images, ...tempFilePaths]
      
      this.setData({
        'formData.images': images.slice(0, 9)
      })
    } catch (e) {
      console.error('选择图片失败', e)
    }
  },

  removeImage(e) {
    const index = e.currentTarget.dataset.index
    const images = [...this.data.formData.images]
    images.splice(index, 1)
    this.setData({
      'formData.images': images
    })
  },

  async submit() {
    const formData = this.data.formData
    
    if (!formData.title.trim()) {
      wx.showToast({
        title: '请输入菜谱名称',
        icon: 'none'
      })
      return
    }
    
    if (!formData.category_id) {
      wx.showToast({
        title: '请选择分类',
        icon: 'none'
      })
      return
    }
    
    if (!formData.image) {
      wx.showToast({
        title: '请上传主图',
        icon: 'none'
      })
      return
    }
    
    if (formData.materials.length === 0) {
      wx.showToast({
        title: '请添加食材',
        icon: 'none'
      })
      return
    }
    
    if (formData.steps.length === 0) {
      wx.showToast({
        title: '请添加制作步骤',
        icon: 'none'
      })
      return
    }
    
    wx.showModal({
      title: '提示',
      content: '确定保存修改吗？',
      success: async (res) => {
        if (res.confirm) {
          this.doSubmit()
        }
      }
    })
  },

  async doSubmit() {
    this.setData({ submitting: true })
    wx.showLoading({
      title: '保存中...',
      mask: true
    })
    
    try {
      const formData = this.data.formData
      const submitData = {
        id: this.data.recipeId,
        title: formData.title,
        category_id: formData.category_id,
        description: formData.description,
        image: formData.image,
        images: formData.images,
        materials: formData.materials,
        steps: formData.steps,
        tips: formData.tips,
        suitable_people: formData.suitable_people,
        cooking_time: formData.cooking_time,
        difficulty: formData.difficulty
      }
      
      const res = await app.request('/recipe/update', submitData, 'POST')
      
      wx.hideLoading()
      this.setData({ submitting: false })
      
      wx.showToast({
        title: '保存成功',
        icon: 'success'
      })
      
      setTimeout(() => {
        wx.navigateBack()
      }, 1500)
    } catch (e) {
      wx.hideLoading()
      this.setData({ submitting: false })
      console.error('保存失败', e)
    }
  }
})
