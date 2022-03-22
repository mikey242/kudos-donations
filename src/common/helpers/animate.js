export const slideNext = (target, cb, timing = 400) => {
  const classes = ['opacity-0', '-translate-x-1']

  // Animate
  target.classList.add(...classes)
  setTimeout(() => {
    target.classList.remove(...classes)
    cb()
  }, timing)
}

export const slidePrev = (target, cb, timing = 400) => {
  const classes = ['opacity-0', 'translate-x-1']

  // Animate
  target.classList.add(...classes)
  setTimeout(() => {
    target.classList.remove(...classes)
    cb()
  }, timing)
}

export const anim = (target, cb, classes, timing = 200) => {
  classes = [...classes, 'opacity-0']
  // Animate
  target.classList.add(...classes)
  setTimeout(() => {
    target.classList.remove(...classes)
    cb()
  }, timing)
}
