import { Head, Link, usePage, useForm, router } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';
import { route } from 'ziggy-js';


export default function Welcome({ users }) {

    const { data, setData, post, errors, processing, recentlySuccessful } = useForm({
        nickname: '',
        username: '',
        password: '',
        _method: 'post',
    });

    const [sortConfig, setSortConfig] = useState({ key: 'id', direction: 'asc' });


    const handleSort = (key) => {
        setSortConfig((prev) => ({
            key,
            direction: prev.key === key && prev.direction === 'asc' ? 'desc' : 'asc',
        }));
    };

    const sortedUsers = useMemo(() => {
        const items = [...users];
        items.sort((a, b) => {
            if (sortConfig.key === 'username' || sortConfig.key === 'nickname') {
                return sortConfig.direction === 'asc'
                    ? a[sortConfig.key].localeCompare(b[sortConfig.key])
                    : b[sortConfig.key].localeCompare(a[sortConfig.key]);
            }

            return sortConfig.direction === 'asc'
                ? Number(a[sortConfig.key]) - Number(b[sortConfig.key])
                : Number(b[sortConfig.key]) - Number(a[sortConfig.key]);
        });
        return items;
    }, [users, sortConfig]);

    const getSortIndicator = (key) => {
        if (sortConfig.key !== key) return ' ↕';
        return sortConfig.direction === 'asc' ? ' ↑' : ' ↓';
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('users.store'), { forceFormData: true });
    };


    const remove = (id) => {
        router.delete(route('users.destroy', id));
    };


    return (
        <>
            <Head title="Welcome" />
            <div className="flex flex-col">
                <table className="table-auto border-collapse mx-auto mt-20 w-1/2">
                    <thead>
                        <tr>
                            <th className="border-none"></th>
                            <th onClick={() => handleSort('id')} className="cursor-pointer select-none border border-slate-300 p-2">
                                ID{getSortIndicator('id')}
                            </th>
                            <th onClick={() => handleSort('username')} className="cursor-pointer select-none border border-slate-300 p-2">
                                Username{getSortIndicator('username')}
                            </th>
                            <th onClick={() => handleSort('nickname')} className="cursor-pointer select-none border border-slate-300 p-2">
                                Name{getSortIndicator('nickname')}
                            </th>
                            {/* <th onClick={() => handleSort('size')} className="cursor-pointer select-none border border-slate-300 p-2">
                                Size{getSortIndicator('size')}
                            </th> */}
                        </tr>
                    </thead>
                    <tbody>
                        {sortedUsers.map((user: any) => (
                            <tr key={user.id}>
                                <td className="border-none flex flex-row-reverse h-28">
                                    <button
                                        type="button"
                                        className="bg-red-500 px-5 py-1 rounded-md cursor-pointer my-10 mr-4"
                                        onClick={() => remove(user.id)}
                                    >
                                        -
                                    </button>
                                </td>
                                <td className="border border-slate-300 text-center">{user.id}</td>
                                <td className="border border-slate-300 text-center">{user.username}</td>
                                <td className="border border-slate-300 text-center">{user.nickname}</td>
                                {/* <td className="border border-slate-300 text-center">{game.password}</td> */}
                            </tr>
                        ))}
                    </tbody>
                </table>

                <form onSubmit={submit}>
                    <div className="w-full flex flex-row gap-10 mx-auto ml-100 mt-32">
                        <div className="flex flex-col gap-5">
                            <input type="text" id="username" className="bg-white rounded-sm text-black" placeholder='Username' onInput={(e) => setData('username', e.target.value)} required />
                            <input type="text" id="nickname" className="bg-white rounded-sm text-black" placeholder='Nickname' onInput={(e) => setData('nickname', e.target.value)} required />
                            <input type="password" id="password" className="bg-white rounded-sm text-black" placeholder='Password' onInput={(e) => setData('password', e.target.value)} required />
                        </div>
                        <button type="submit" className="bg-green-500 px-5 py-1 rounded-md cursor-pointer my-10">
                            {processing ? 'Saving' : '+'}</button>
                    </div>
                </form>
            </div>
        </>
    );
}
