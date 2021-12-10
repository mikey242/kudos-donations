import classNames from "classnames"

const Button = (props) => {

    return(
        <button
            onClick={props.onClick}
            type={props.type}
            className={classNames(props.className, "border-none bg-primary hover:bg-primary-dark w-auto h-auto inline-flex items-center select-none py-3 px-5 rounded-lg cursor-pointer shadow-none transition ease-in-out focus:ring-primary focus:ring focus:ring-offset-2 text-center text-white leading-normal font-normal normal-case no-underline")}
            aria-label={props.children}
        >
            {props.children}
        </button>

    )

}

export {Button}