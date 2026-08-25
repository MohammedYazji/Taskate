import { createContext, useContext, useState, useEffect } from "react";
import { router } from "@inertiajs/react";

const LoadingContext = createContext({ loading: false });

export function useLoading() {
    return useContext(LoadingContext);
}

export function LoadingProvider({ children }) {
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        const start = () => setLoading(true);
        const finish = () => setLoading(false);

        router.on("start", start);
        router.on("finish", finish);

        return () => {
            router.on("start", () => {});
            router.on("finish", () => {});
        };
    }, []);

    return (
        <LoadingContext.Provider value={{ loading }}>
            {children}
        </LoadingContext.Provider>
    );
}
