export default function InputLabel({ value, children, className = '' }) {
    return (
        <label className={'block font-medium text-sm text-gray-700 ' + className}>
            {value || children}
        </label>
    );
}
