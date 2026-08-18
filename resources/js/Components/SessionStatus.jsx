export default function SessionStatus({ status }) {
    if (!status) return null;

    return (
        <div className="font-medium text-sm text-green-600">
            {status}
        </div>
    );
}
