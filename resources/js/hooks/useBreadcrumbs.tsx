import React, { createContext, useContext, useState } from 'react';
import { BreadcrumbItem } from '@/types';

type BreadcrumbContextValue = {
    breadcrumbs: BreadcrumbItem[];
    setBreadcrumbs: (breadcrumbs: BreadcrumbItem[]) => void;
};

const BreadcrumbsContext = createContext<BreadcrumbContextValue|undefined>(undefined);

export const BreadCrumbProvider = ({ children }: { children: React.ReactNode }) => {
    const [breadcrumbs, setBreadcrumbs] = useState<BreadcrumbItem[]>([]);
    return (
        <BreadcrumbsContext.Provider value={{ breadcrumbs, setBreadcrumbs }}>
            {children}
        </BreadcrumbsContext.Provider>
    );
}

export const useBreadcrumbContext = () => {
    const context = useContext(BreadcrumbsContext);
    if (!context) throw new Error('useBreadcrumbContext must be used within a BreadCrumbProvider');
    return context;
}
