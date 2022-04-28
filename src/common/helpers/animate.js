export const anim = (target, cb, classes, timing = 200) => {
	classes = [...classes, 'opacity-0'];
	// Animate
	target.classList.add(...classes);
	setTimeout(() => {
		target.classList.remove(...classes);
		cb();
	}, timing);
};
