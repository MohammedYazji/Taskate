export default function PrimaryButton({ className = '', disabled, children, ...props }) {
    return (
        <button
            {...props}
            type={props.type || 'submit'}
            disabled={disabled}
            className={
                'inline-flex items-center px-4 py-2 bg-brand-500 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-brand-600 active:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition ease-in-out duration-150' +
                (disabled ? ' opacity-25' : '') +
                ' ' +
                className
            }
        >
            {children}
        </button>
    );
}
