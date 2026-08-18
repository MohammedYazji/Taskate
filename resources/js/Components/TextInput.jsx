export default function TextInput({ type = 'text', className = '', ...props }) {
    return (
        <input
            type={type}
            className={
                'border-gray-300 focus:border-brand-500 focus:ring-brand-500 rounded-lg shadow-sm ' +
                className
            }
            {...props}
        />
    );
}
