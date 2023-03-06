import mitt from 'mitt'

function createEmitter() {
  const emitter = mitt()

  emitter.install = (app) => {
    app.config.globalProperties.$emitter = emitter
  }

  return emitter
}

export { createEmitter }
