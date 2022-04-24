import { createContext } from "react";
import { toast, ToastContainer } from "react-toastify";

const ToastContext = createContext(toast);

export default ToastContext;

export const ToastContextProvider = ({ children }) => {
    return (
        <ToastContext.Provider value={toast}>
            { children }
            <ToastContainer
                autoClose={5000}
                position="bottom-right"
                theme="light"
                hideProgressBar={true}
                toastClassName="shadow"
                pauseOnFocusLoss={false}
                pauseOnHover={false}
            />
        </ToastContext.Provider>
    );
}